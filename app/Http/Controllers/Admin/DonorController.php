<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Donor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DonorController extends Controller
{
    public function landing()
    {
        return view('admin.penderma.landing');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $homepageFilter = $request->query('homepage', 'all');
        if (! in_array($homepageFilter, ['all', 'displayed', 'hidden'], true)) {
            $homepageFilter = 'all';
        }

        $jenisFilter = $request->query('jenis', 'all');
        if (! in_array($jenisFilter, ['all', 'individu', 'organisasi'], true)) {
            $jenisFilter = 'all';
        }

        $sortBy = $request->query('sort', 'latest');
        if (! in_array($sortBy, ['latest', 'oldest', 'name_az', 'ranking'], true)) {
            $sortBy = 'latest';
        }

        $donorsQuery = Donor::query()
            ->select('donors.*')
            ->with(['user', 'address'])
            ->join('users', 'users.id', '=', 'donors.user_id')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                      ->orWhere('users.email', 'like', "%{$search}%");
                });
            })
            ->when($homepageFilter === 'displayed', function ($query) {
                $query->where('donors.show_on_homepage', true);
            })
            ->when($homepageFilter === 'hidden', function ($query) {
                $query->where('donors.show_on_homepage', false);
            })
            ->when($jenisFilter === 'individu', function ($query) {
                $query->where('donors.donor_type', 'individu');
            })
            ->when($jenisFilter === 'organisasi', function ($query) {
                $query->whereIn('donors.donor_type', ['syarikat', 'ngo']);
            });

        match ($sortBy) {
            'oldest' => $donorsQuery
                ->orderBy('users.created_at', 'asc')
                ->orderBy('donors.id', 'asc'),
            'name_az' => $donorsQuery
                ->orderBy('users.name', 'asc')
                ->orderBy('donors.id', 'asc'),
            'ranking' => $donorsQuery
                ->orderByRaw(
                    'CASE WHEN donors.show_on_homepage = ? AND donors.homepage_order IS NOT NULL AND donors.homepage_order > 0 THEN 0 ELSE 1 END ASC',
                    [true]
                )
                ->orderByRaw(
                    'CASE WHEN donors.show_on_homepage = ? AND donors.homepage_order IS NOT NULL AND donors.homepage_order > 0 THEN donors.homepage_order END ASC',
                    [true]
                )
                ->orderBy('donors.id', 'asc'),
            default => $donorsQuery
                ->orderBy('users.created_at', 'desc')
                ->orderBy('donors.id', 'desc'),
        };

        $donors = $donorsQuery
            ->paginate(15)
            ->withQueryString();

        $oldEditingDonor = null;
        $oldEditingUserId = $request->old('_editing_user_id');

        if ($oldEditingUserId) {
            $oldEditingDonor = Donor::with(['user', 'address'])
                ->where('user_id', $oldEditingUserId)
                ->first();
        }

        return view('admin.penderma.index', compact(
            'donors',
            'search',
            'homepageFilter',
            'jenisFilter',
            'sortBy',
            'oldEditingDonor'
        ));
    }

    public function create()
    {
        return view('admin.penderma.create');
    }

    public function store(Request $request)
    {
        $phoneRule = ['required', 'string', 'regex:/^01\d{8,9}$/'];
        $countryOptions = $this->countryOptions();
        $stateOptions = self::stateOptions();

        $rules = [
            'donor_type'        => 'required|in:individu,syarikat,ngo',
            'preferred_contact' => 'required|in:email,phone',
            'address_line_1'    => 'required|string|max:255',
            'city'              => 'required|string|max:100',
            'postcode'          => ['required', 'regex:/^\d{5}$/'],
            'state'             => ['required', 'string', 'max:100', Rule::in($stateOptions)],
            'country'           => ['required', Rule::in($countryOptions)],
            'country_other'     => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf(fn () => $request->input('country') === 'Lain-lain'),
            ],

            'logo'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'support_document'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'homepage_order'    => $this->homepageOrderRules($request),
            'show_on_homepage'  => 'nullable|boolean',
        ];

        if ($request->donor_type === 'individu') {
            $rules['name']  = 'required|string|max:255';
            $rules['email'] = 'required|email';
            $rules['phone'] = $phoneRule;
        }

        if ($request->donor_type === 'syarikat') {
            $rules['company_name'] = 'required|string|max:255';
            $rules['company_email'] = 'required|email';
            $rules['company_phone'] = $phoneRule;
            $rules['representative_name'] = 'required|string|max:255';
        }

        if ($request->donor_type === 'ngo') {
            $rules['ngo_name'] = 'required|string|max:255';
            $rules['ngo_email'] = 'required|email';
            $rules['ngo_phone'] = $phoneRule;
            $rules['representative_name'] = 'required|string|max:255';
        }

        $messages = [
            'required' => ':attribute wajib diisi.',
            'email' => ':attribute mesti menggunakan format emel yang sah.',
            'in' => ':attribute yang dipilih tidak sah.',
            'string' => ':attribute mesti dalam format teks.',
            'max' => ':attribute tidak boleh melebihi :max aksara.',
            'integer' => ':attribute mesti dalam format nombor bulat.',
            'min' => ':attribute mesti sekurang-kurangnya :min.',
            'boolean' => ':attribute mesti bernilai ya atau tidak.',
            'image' => 'Logo penderma mesti dalam format imej.',
            'logo.mimes' => 'Logo penderma mesti dalam format JPG, JPEG, PNG atau WEBP.',
            'logo.max' => 'Logo penderma tidak boleh melebihi 2MB.',
            'support_document.file' => 'Dokumen sokongan mesti dimuat naik sebagai fail.',
            'support_document.mimes' => 'Dokumen sokongan mesti dalam format PDF, JPG, JPEG atau PNG.',
            'support_document.max' => 'Dokumen sokongan tidak boleh melebihi 5MB.',
            'phone.regex' => 'Sila masukkan nombor telefon yang sah. Contoh: 0123456789',
            'company_phone.regex' => 'Sila masukkan nombor telefon yang sah. Contoh: 0123456789',
            'ngo_phone.regex' => 'Sila masukkan nombor telefon yang sah. Contoh: 0123456789',
            'postcode.regex' => 'Sila masukkan poskod yang sah (5 digit).',
            'state.required' => 'Sila pilih negeri.',
            'state.in' => 'Sila pilih negeri.',
            'country_other.required' => 'Sila nyatakan negara.',
            'homepage_order.unique' => 'Ranking ini telah digunakan oleh penderma lain. Sila pilih ranking yang lain.',
        ];

        $attributes = [
            'donor_type' => 'Jenis penderma',
            'preferred_contact' => 'Saluran komunikasi pilihan',
            'address_line_1' => 'Alamat baris 1',
            'city' => 'Bandar',
            'postcode' => 'Poskod',
            'state' => 'Negeri',
            'country' => 'Negara',
            'country_other' => 'Negara',
            'logo' => 'Logo penderma',
            'support_document' => 'Dokumen sokongan',
            'homepage_order' => 'Ranking',
            'show_on_homepage' => 'Paparan homepage',
            'name' => 'Nama penuh',
            'email' => 'Emel',
            'phone' => 'No telefon',
            'company_name' => 'Nama syarikat',
            'company_email' => 'Emel syarikat',
            'company_phone' => 'No telefon syarikat',
            'ngo_name' => 'Nama NGO',
            'ngo_email' => 'Emel NGO',
            'ngo_phone' => 'No telefon NGO',
            'representative_name' => 'Nama wakil organisasi',
        ];

        $request->validate($rules, $messages, $attributes);

        $showOnHomepage = $request->boolean('show_on_homepage');
        $homepageOrder = $this->homepageOrderValue($request);

        if ($request->donor_type === 'individu') {
            $name = $request->name;
            $email = $request->email;
            $phone = $request->phone;
            $representativeName = null;
        }

        if ($request->donor_type === 'syarikat') {
            $name = $request->company_name;
            $email = $request->company_email;
            $phone = $request->company_phone;
            $representativeName = $request->representative_name;
        }

        if ($request->donor_type === 'ngo') {
            $name = $request->ngo_name;
            $email = $request->ngo_email;
            $phone = $request->ngo_phone;
            $representativeName = $request->representative_name;
        }

        $country = $this->resolvedCountry($request);

        if (User::where('email', $email)->exists()) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'Emel ini telah digunakan oleh akaun lain'
                ]);
        }

        DB::beginTransaction();

        try {
            $logoPath = null;
            $supportDocumentPath = null;

            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('penderma-logo', 'public');
            }

            if ($request->hasFile('support_document')) {
                $supportDocumentPath = $request->file('support_document')
                    ->store('donor-documents', 'public');
            }

            $user = User::create([
                'name'           => $name,
                'email'          => $email,
                'password'       => Hash::make(str()->random(16)),
                'role'           => 'penderma',
                'account_status' => 'pending',
            ]);

            $donor = Donor::create([
                'user_id'           => $user->id,
                'donor_type'        => $request->donor_type,
                'representative_name' => $representativeName,
                'phone'             => $phone,
                'preferred_contact' => $request->preferred_contact,
                'admin_note'        => $request->admin_note,

                'logo'              => $logoPath,
                'support_document'  => $supportDocumentPath,
                'homepage_order'    => $homepageOrder,
                'show_on_homepage'  => $showOnHomepage,
            ]);

            $donor->address()->create([
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'city'           => $request->city,
                'postcode'       => $request->postcode,
                'state'          => $request->state,
                'country'        => $country,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.penderma.index')
                ->with('success', 'Penderma baharu berjaya didaftarkan.');

        } catch (\Throwable $e) {
            DB::rollBack();

            if (!empty($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }

            if (!empty($supportDocumentPath)) {
                Storage::disk('public')->delete($supportDocumentPath);
            }

            return back()
                ->withInput()
                ->withErrors([
                    'error' => 'Ralat sistem: ' . $e->getMessage()
                ]);
        }
    }

    public function show(User $user)
    {
        if ($user->role !== 'penderma') {
            abort(404);
        }

        $donor = Donor::with(['user', 'address'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        return view('admin.penderma.show', compact('donor'));
    }

    public function edit(User $user)
    {
        if ($user->role !== 'penderma') {
            abort(404);
        }

        $donor = Donor::with(['user', 'address'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        return view('admin.penderma.edit', compact('user', 'donor'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role !== 'penderma') {
            abort(404);
        }

        $donor = Donor::with('address')
            ->where('user_id', $user->id)
            ->firstOrFail();

        $countryOptions = $this->countryOptions();
        $stateOptions = self::stateOptions();

        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'account_status'    => 'nullable|in:pending,invited,active',
            'phone'             => ['required', 'string', 'regex:/^01\d{8,9}$/'],
            'representative_name' => 'nullable|string|max:255',
            'preferred_contact' => 'nullable|in:email,phone',
            'admin_note'        => 'nullable|string',

            'address_line_1'    => 'nullable|string|max:255',
            'address_line_2'    => 'nullable|string|max:255',
            'city'              => 'nullable|string|max:100',
            'postcode'          => ['nullable', 'regex:/^\d{5}$/'],
            'state'             => ['required', 'string', 'max:100', Rule::in($stateOptions)],
            'country'           => ['nullable', Rule::in($countryOptions)],
            'country_other'     => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf(fn () => $request->input('country') === 'Lain-lain'),
            ],

            'logo'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'homepage_order'    => $this->homepageOrderRules($request, $donor),
            'show_on_homepage'  => 'nullable|boolean',
            'support_document'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'required' => ':attribute wajib diisi.',
            'email' => ':attribute mesti menggunakan format emel yang sah.',
            'unique' => ':attribute ini telah digunakan oleh akaun lain.',
            'in' => ':attribute yang dipilih tidak sah.',
            'string' => ':attribute mesti dalam format teks.',
            'max' => ':attribute tidak boleh melebihi :max aksara.',
            'integer' => ':attribute mesti dalam format nombor bulat.',
            'min' => ':attribute mesti sekurang-kurangnya :min.',
            'boolean' => ':attribute mesti bernilai ya atau tidak.',
            'image' => 'Logo penderma mesti dalam format imej.',
            'mimes' => ':attribute mesti menggunakan format yang dibenarkan.',
            'logo.mimes' => 'Logo penderma mesti dalam format JPG, JPEG, PNG atau WEBP.',
            'logo.max' => 'Logo penderma tidak boleh melebihi 2MB.',
            'support_document.file' => 'Dokumen sokongan mesti dimuat naik sebagai fail.',
            'support_document.mimes' => 'Dokumen sokongan mesti dalam format PDF, JPG, JPEG atau PNG.',
            'support_document.max' => 'Dokumen sokongan tidak boleh melebihi 5MB.',
            'phone.required' => 'Sila masukkan nombor telefon yang sah. Contoh: 0123456789',
            'phone.regex' => 'Sila masukkan nombor telefon yang sah. Contoh: 0123456789',
            'postcode.regex' => 'Sila masukkan poskod yang sah (5 digit).',
            'state.required' => 'Sila pilih negeri.',
            'state.in' => 'Sila pilih negeri.',
            'country_other.required' => 'Sila nyatakan negara.',
            'homepage_order.unique' => 'Ranking ini telah digunakan oleh penderma lain. Sila pilih ranking yang lain.',
        ], [
            'name' => 'Nama',
            'email' => 'Emel',
            'account_status' => 'Status',
            'phone' => 'No telefon',
            'representative_name' => 'Nama wakil organisasi',
            'preferred_contact' => 'Saluran komunikasi pilihan',
            'admin_note' => 'Catatan admin',
            'address_line_1' => 'Alamat baris 1',
            'address_line_2' => 'Alamat baris 2',
            'city' => 'Bandar',
            'postcode' => 'Poskod',
            'state' => 'Negeri',
            'country' => 'Negara',
            'country_other' => 'Negara',
            'logo' => 'Logo penderma',
            'homepage_order' => 'Ranking',
            'show_on_homepage' => 'Paparan homepage',
            'support_document' => 'Dokumen sokongan',
        ]);

        $country = $this->resolvedCountry($request, $donor->address?->country ?? 'Malaysia');
        $showOnHomepage = $request->boolean('show_on_homepage');
        $homepageOrder = $this->homepageOrderValue($request);
        $newLogoPath = null;
        $newSupportDocumentPath = null;

        DB::beginTransaction();

        try {
            $user->update([
                'name'           => $request->name,
                'email'          => $request->email,
                'account_status' => $request->input('account_status', $user->account_status),
            ]);

            $oldLogoPath = $donor->logo;
            $logoPath = $donor->logo;

            if ($request->hasFile('logo')) {
                $newLogoPath = $request->file('logo')->store('donor-logos', 'public');
                $logoPath = $newLogoPath;
            }

            $oldSupportDocumentPath = $donor->support_document;
            $supportDocumentPath = $donor->support_document;

            if ($request->hasFile('support_document')) {
                $newSupportDocumentPath = $request->file('support_document')
                    ->store('donor-documents', 'public');

                $supportDocumentPath = $newSupportDocumentPath;
            }

            $donor->update([
                'phone'             => $request->phone,
                'representative_name' => $request->representative_name,
                'preferred_contact' => $request->preferred_contact ?? $donor->preferred_contact,
                'admin_note'        => $request->admin_note,

                'logo'              => $logoPath,
                'support_document'  => $supportDocumentPath,
                'homepage_order'    => $homepageOrder,
                'show_on_homepage'  => $showOnHomepage,
            ]);

            if ($donor->address) {
                $donor->address->update([
                    'address_line_1' => $request->address_line_1,
                    'address_line_2' => $request->address_line_2,
                    'city'           => $request->city,
                    'postcode'       => $request->postcode,
                    'state'          => $request->state,
                    'country'        => $country,
                ]);
            }

            DB::commit();

            if ($newSupportDocumentPath && $oldSupportDocumentPath) {
                Storage::disk('public')->delete($oldSupportDocumentPath);
            }

            if ($newLogoPath && $oldLogoPath) {
                Storage::disk('public')->delete($oldLogoPath);
            }

            return redirect()
                ->route('admin.penderma.index')
                ->with('success', 'Maklumat penderma berjaya dikemaskini.');

        } catch (\Throwable $e) {
            DB::rollBack();

            if ($newSupportDocumentPath) {
                Storage::disk('public')->delete($newSupportDocumentPath);
            }

            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }

            return back()
                ->withInput()
                ->withErrors([
                    'error' => 'Gagal kemaskini penderma. Sila cuba semula atau hubungi pentadbir sistem.'
                ]);
        }
    }

    public function activate(User $user)
    {
        if ($user->role !== 'penderma') {
            abort(404);
        }

        if ($user->account_status === 'active') {
            return back()->with('info', 'Akaun penderma sudah aktif.');
        }

        $user->update([
            'account_status' => 'invited',
        ]);

        Password::sendResetLink([
            'email' => $user->email,
        ]);

        return back()->with(
            'success',
            'Jemputan telah dihantar. Penderma perlu mengaktifkan akaun melalui emel.'
        );
    }

    public function resend(User $user)
    {
        if ($user->role !== 'penderma') {
            abort(404);
        }

        if (! in_array($user->account_status, ['pending', 'invited'], true)) {
            return back()->with(
                'info',
                'Akaun ini tidak memerlukan email pengesahan.'
            );
        }

        $wasPending = $user->account_status === 'pending';

        if ($wasPending) {
            $user->update([
                'account_status' => 'invited',
            ]);
        }

        Password::sendResetLink([
            'email' => $user->email,
        ]);

        return back()->with(
            'success',
            $wasPending
                ? 'Email pengesahan berjaya dihantar kepada penderma.'
                : 'Email pengesahan berjaya dihantar semula.'
        );
    }

    public function destroy(User $user)
    {
        if ($user->role !== 'penderma') {
            abort(404);
        }

        if (
            $user->sumbangans()->exists()
            || $user->cashDonations()->exists()
            || $user->physicalDonations()->exists()
        ) {
            return back()->with(
                'error',
                'Penderma ini mempunyai rekod sumbangan dan tidak boleh dipadam.'
            );
        }

        DB::beginTransaction();

        try {
            if ($user->donor) {
                if ($user->donor->logo) {
                    Storage::disk('public')->delete($user->donor->logo);
                }

                if ($user->donor->support_document) {
                    Storage::disk('public')->delete($user->donor->support_document);
                }

                $user->donor->address()?->delete();
                $user->donor->delete();
            }

            $user->delete();

            DB::commit();

            return back()->with(
                'success',
                'Akaun penderma berjaya dipadam.'
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Gagal memadam penderma: ' . $e->getMessage()
            ]);
        }
    }

    private function countryOptions(): array
    {
        return [
            'Malaysia',
            'Singapore',
            'Indonesia',
            'Thailand',
            'Brunei',
            'Lain-lain',
        ];
    }

    public static function stateOptions(): array
    {
        return [
            'Johor',
            'Kedah',
            'Kelantan',
            'Melaka',
            'Negeri Sembilan',
            'Pahang',
            'Perak',
            'Perlis',
            'Pulau Pinang',
            'Sabah',
            'Sarawak',
            'Selangor',
            'Terengganu',
            'Wilayah Persekutuan Kuala Lumpur',
            'Wilayah Persekutuan Putrajaya',
            'Wilayah Persekutuan Labuan',
        ];
    }

    private function resolvedCountry(Request $request, string $fallback = 'Malaysia'): string
    {
        if ($request->input('country') === 'Lain-lain') {
            return trim((string) $request->input('country_other'));
        }

        $country = trim((string) $request->input('country'));

        return $country !== '' ? $country : $fallback;
    }

    private function homepageOrderRules(Request $request, ?Donor $donor = null): array
    {
        $showOnHomepage = $request->boolean('show_on_homepage');

        $rules = [
            Rule::requiredIf($showOnHomepage),
            'nullable',
            'integer',
            'min:1',
        ];

        if ($showOnHomepage) {
            $uniqueRule = Rule::unique('donors', 'homepage_order');

            if ($donor) {
                $uniqueRule->ignore($donor->id);
            }

            $rules[] = $uniqueRule;
        }

        return $rules;
    }

    private function homepageOrderValue(Request $request): ?int
    {
        if (! $request->boolean('show_on_homepage')) {
            return null;
        }

        return (int) $request->input('homepage_order');
    }
}

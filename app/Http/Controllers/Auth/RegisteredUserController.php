<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    /**
     * Papar borang pendaftaran
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Simpan pendaftaran pelajar
     */
    public function store(Request $request)
    {
        $request->merge([
            'matrik' => strtoupper(trim((string) $request->input('matrik'))),
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        // 1️⃣ VALIDATION ASAS
        $validated = $request->validate([
            'name' => 'required|string|max:255',

            'matrik' => [
                'required',
                'string',
                'unique:users,matrik',
                'regex:/^[Aa]\d{6}$/'
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email',
                'regex:/@siswa\.ukm\.edu\.my$/'
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9\s]/',
                'confirmed',
            ],
        ], [
            'email.regex'        => 'Sila gunakan email rasmi siswa (@siswa.ukm.edu.my)',
            'matrik.regex'       => 'Mesti bermula dengan huruf A dan diikuti 6 digit',
            'password.min'       => 'Kata laluan mesti mempunyai minimum 8 aksara, huruf besar, huruf kecil, nombor dan simbol.',
            'password.regex'     => 'Kata laluan mesti mempunyai minimum 8 aksara, huruf besar, huruf kecil, nombor dan simbol.',
            'password.confirmed' => 'Kata laluan tidak sepadan',
        ]);

        // 2️⃣ NORMALISASI (PENTING)
        $matrik = strtolower($validated['matrik']);
        $email  = $validated['email'];

        // 3️⃣ CHECK PADANAN MATRIK ↔ EMAIL
        $emailPrefix = explode('@', $email)[0];

        if ($emailPrefix !== $matrik) {
            return back()
                ->withErrors([
                    'email' => 'Email mestilah menggunakan nombor matrik yang sama'
                ])
                ->withInput();
        }

        // 4️⃣ SIMPAN USER (AUTO MASUK MATRIK)
        $user = User::create([
            'name'           => $validated['name'],
            'matrik'         => $validated['matrik'], // A208972
            'email'          => $email,
            'password'       => Hash::make($validated['password']),
            'role'           => 'pelajar',
            'account_status' => 'active',
        ]);

        try {
            event(new Registered($user));
        } catch (\Throwable $exception) {
            Log::warning('Gagal menghantar emel pengesahan pelajar selepas pendaftaran.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('login')
                ->with('warning', 'Pendaftaran berjaya, tetapi emel pengesahan tidak dapat dihantar. Sila log masuk dan hantar semula emel pengesahan.');
        }

        // 5️⃣ REDIRECT KE LOGIN
        return redirect()->route('login')
            ->with('success', 'Pendaftaran berjaya. Sila semak emel untuk pengesahan akaun, kemudian log masuk untuk meneruskan.');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
        ]);

        $identifier = trim($request->identifier);
        $user = null;

        if (preg_match('/^[a-z]\d{6}@siswa\.ukm\.edu\.my$/i', $identifier)) {
            return back()->withErrors([
                'identifier' => 'Untuk pelajar, sila masukkan nombor matrik sahaja tanpa @siswa.ukm.edu.my. Contoh: AXXXXXX'
            ])->withInput();
        }

        // Email login: penderma/admin only.
        if (Str::contains($identifier, '@')) {
            $user = User::where('email', strtolower($identifier))
                ->whereIn('role', ['penderma', 'admin'])
                ->first();
        }
        // No_matrik login: pelajar only.
        else {
            $user = User::where('matrik', strtoupper($identifier))
                ->where('role', 'pelajar')
                ->first();
        }
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'identifier' => 'Emel / No Matrik atau kata laluan tidak sah'
            ])->withInput();
        }

        // ❌ Akaun belum aktif
        if ($user->account_status !== 'active') {
            Auth::logout();

            return back()->withErrors([
                'identifier' => match ($user->account_status) {
                    'pending'  => 'Akaun anda masih menunggu kelulusan pentadbir.',
                    'invited'  => 'Sila semak emel anda untuk mengaktifkan akaun.',
                    default    => 'Akaun anda tidak aktif.',
                }
            ]);
        }

        // ✅ Login berjaya
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return match ($user->role) {
            'pelajar' => redirect()->intended('/dashboard/pelajar'),
            'penderma' => redirect()->intended('/dashboard/penderma'),
            'admin' => redirect()->intended('/dashboard/admin'),
        };
    }
}

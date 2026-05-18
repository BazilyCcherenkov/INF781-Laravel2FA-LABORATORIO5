<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorVerifyController extends Controller
{
    public function show()
    {
        return view('two-factor.verify');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $user = $request->user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Código OTP inválido. Intenta de nuevo.']);
        }

        $request->session()->put('two_factor_verified', true);

        return redirect()->intended(route('dashboard'));
    }

    public function verifyWithBackupCode(Request $request)
    {
        $request->validate(['backup_code' => 'required|string']);

        $user = $request->user();
        $inputCode = strtoupper($request->backup_code);

        $hashedCodes = json_decode($user->backup_codes ?? '[]', true);

        foreach ($hashedCodes as $index => $hashedCode) {
            if (password_verify($inputCode, $hashedCode)) {
                unset($hashedCodes[$index]);
                $user->update(['backup_codes' => json_encode(array_values($hashedCodes))]);

                $request->session()->put('two_factor_verified', true);
                return redirect()->intended(route('dashboard'))->with('status', 'Accediste usando un código de respaldo.');
            }
        }

        return back()->withErrors(['backup_code' => 'Código de respaldo inválido.']);
    }
}
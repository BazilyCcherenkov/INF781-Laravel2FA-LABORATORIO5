<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    private function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    private function hashBackupCodes(array $codes): array
    {
        return array_map(fn($code) => password_hash($code, PASSWORD_BCRYPT), $codes);
    }

    public function show(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        if (!$user->two_factor_secret) {
            $secret = $google2fa->generateSecretKey();
            $user->update(['two_factor_secret' => $secret]);
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('two-factor.setup', [
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $user->two_factor_secret,
            'enabled' => $user->two_factor_enabled,
            'showBackupCodes' => $request->session()->get('show_backup_codes', false),
            'backupCodes' => $request->session()->get('backup_codes', []),
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $user = $request->user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'El código OTP es inválido.']);
        }

        $plainCodes = $this->generateBackupCodes();
        $hashedCodes = $this->hashBackupCodes($plainCodes);

        $user->update([
            'two_factor_enabled' => true,
            'backup_codes' => json_encode($hashedCodes),
        ]);

        $request->session()->put('show_backup_codes', true);
        $request->session()->put('backup_codes', $plainCodes);

        return redirect()->route('two-factor.setup')
            ->with('status', '2FA activado correctamente.');
    }

    public function regenerateBackupCodes(Request $request)
    {
        $user = $request->user();
        $plainCodes = $this->generateBackupCodes();
        $hashedCodes = $this->hashBackupCodes($plainCodes);

        $user->update(['backup_codes' => json_encode($hashedCodes)]);

        $request->session()->put('show_backup_codes', true);
        $request->session()->put('backup_codes', $plainCodes);

        return redirect()->route('two-factor.setup')
            ->with('status', 'Códigos de respaldo regenerados.');
    }

    public function disable(Request $request)
    {
        $request->user()->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'backup_codes' => null,
        ]);

        $request->session()->forget(['show_backup_codes', 'backup_codes']);

        return redirect()->route('two-factor.setup')
            ->with('status', '2FA desactivado.');
    }

    public function verifyBackupCode(Request $request)
    {
        return app(TwoFactorVerifyController::class)->verifyWithBackupCode($request);
    }
}
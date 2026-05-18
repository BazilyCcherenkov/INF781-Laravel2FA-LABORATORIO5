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

        $user->update(['two_factor_enabled' => true]);

        return redirect()->route('two-factor.setup')
            ->with('status', '2FA activado correctamente.');
    }

    public function disable(Request $request)
    {
        $request->user()->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
        ]);

        return redirect()->route('two-factor.setup')
            ->with('status', '2FA desactivado.');
    }
}
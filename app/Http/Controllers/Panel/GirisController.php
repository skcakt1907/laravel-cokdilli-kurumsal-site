<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Panel girişi.
 *
 * Düz PHP sürümünde 5 denemeden sonra 15 dakikalık oturum tabanlı kilit
 * vardı; oturum silinince kilit de gidiyordu. Burada Laravel'in
 * RateLimiter'ı kullanılıyor: kilit kullanıcı adı + IP kırılımında
 * sunucu tarafında tutulur, tarayıcı kapatmak işe yaramaz.
 */
class GirisController extends Controller
{
    private const DENEME_HAKKI = 5;
    private const KILIT_SANIYE = 900;   // 15 dakika

    public function form(): View
    {
        return view('panel.giris');
    }

    public function gir(Request $istek): RedirectResponse
    {
        $veri = $istek->validate([
            'kullanici' => ['required', 'string', 'max:60'],
            'sifre'     => ['required', 'string'],
        ], [], [
            'kullanici' => 'kullanıcı adı',
            'sifre'     => 'şifre',
        ]);

        $anahtar = $this->kilitAnahtari($istek);

        if (RateLimiter::tooManyAttempts($anahtar, self::DENEME_HAKKI)) {
            $kalan = ceil(RateLimiter::availableIn($anahtar) / 60);

            throw ValidationException::withMessages([
                'kullanici' => "Çok fazla başarısız deneme. {$kalan} dakika sonra tekrar deneyin.",
            ]);
        }

        $girildi = Auth::attempt(
            ['kullanici' => $veri['kullanici'], 'password' => $veri['sifre'], 'durum' => 1],
            $istek->boolean('hatirla'),
        );

        if (!$girildi) {
            RateLimiter::hit($anahtar, self::KILIT_SANIYE);

            throw ValidationException::withMessages([
                'kullanici' => 'Kullanıcı adı veya şifre hatalı.',
            ]);
        }

        RateLimiter::clear($anahtar);
        $istek->session()->regenerate();          // oturum sabitleme koruması

        Auth::user()->forceFill(['son_giris' => now()])->save();

        return redirect()->intended(route('panel.pano'));
    }

    public function cik(Request $istek): RedirectResponse
    {
        Auth::logout();
        $istek->session()->invalidate();
        $istek->session()->regenerateToken();

        return redirect()->route('panel.giris');
    }

    /** Kilit kullanıcı adı + IP kırılımında tutulur. */
    private function kilitAnahtari(Request $istek): string
    {
        return 'panel-giris|' . mb_strtolower((string) $istek->input('kullanici')) . '|' . $istek->ip();
    }
}

<?php

namespace App\Http\Middleware;

use App\Support\Dil;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aktif dili belirler: ?lang= > çerez > varsayılan.
 *
 * Düz PHP sürümündeki lang() fonksiyonunun karşılığı; aynı çerez adını
 * (`site_lang`) kullanır, böylece iki sürüm arasında geçiş yapan
 * ziyaretçinin dil tercihi korunur.
 */
class DilSec
{
    /**
     * Aktif diller: kod => yerel ad.
     * Kaynak config/diller.php — kodun hiçbir yerinde dil listesi sabit değil.
     */
    public static function diller(): array
    {
        $cikti = [];

        foreach (Dil::aktif() as $kod => $tanim) {
            $cikti[$kod] = $tanim['yerel'] ?? $tanim['ad'] ?? strtoupper($kod);
        }

        return $cikti;
    }

    public function handle(Request $istek, Closure $sonraki): Response
    {
        $dil = $this->belirle($istek);
        app()->setLocale($dil);

        $yanit = $sonraki($istek);

        // URL ile dil değiştirildiyse tercihi bir yıl hatırla.
        if ($istek->query('lang') === $dil) {
            $yanit->headers->setCookie(cookie('site_lang', $dil, 60 * 24 * 365));
        }

        return $yanit;
    }

    private function belirle(Request $istek): string
    {
        $istenen = $istek->query('lang');
        if (Dil::gecerliMi(is_string($istenen) ? $istenen : null)) {
            return $istenen;
        }

        $cerez = $istek->cookie('site_lang');
        if (Dil::gecerliMi(is_string($cerez) ? $cerez : null)) {
            return $cerez;
        }

        return Dil::temel();
    }
}

<?php

namespace App\Providers;

use App\Models\Ayar;
use App\Models\Hizmet;
use App\Models\Yazi;
use App\Support\Metin;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Site görünümlerinin ortak verisi ve Blade kısayolları.
 *
 * Düz PHP sürümünde header.php her sayfada menü verisini kendi çekiyordu;
 * burada View composer ile tek yerden veriliyor.
 */
class GorunumServisi extends ServiceProvider
{
    public function boot(): void
    {
        // Site şablonlarının hepsinde lazım olan veriler
        View::composer(['site.*', 'components.site.*'], function ($gorunum) {
            $gorunum->with([
                'menuFaaliyetler' => Hizmet::yayinda()->sirali()->get()->groupBy(fn ($h) => $h->d('grup') ?: '—'),
                'haberVar'        => Yazi::yayinda()->exists(),
                'siteAdi'         => Ayar::al('site_adi'),
            ]);
        });

        // @zengin($metin) — uzun metin alanlarını biçimlendirir
        Blade::directive('zengin', fn ($ifade) => "<?php echo \App\Support\Metin::zengin($ifade); ?>");

        // @gorsel($yol) — görsel adresini çözer
        Blade::directive('gorsel', fn ($ifade) => "<?php echo e(\App\Support\Metin::gorselUrl($ifade)); ?>");

        // @tarih($tarih) — aktif dile göre tarih
        Blade::directive('tarih', fn ($ifade) => "<?php echo e(\App\Support\Metin::tarih($ifade)); ?>");
    }
}

<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Düz PHP sürümünden taşınan metin yardımcıları.
 *
 * Aynı davranışı korumak önemli: içerik veritabanında bu kurallara göre
 * yazılmış durumda, davranış değişirse yayındaki metinler bozulur.
 */
class Metin
{
    /**
     * Uzun metin alanlarını güvenli HTML'e çevirir.
     *   boş satır  -> yeni paragraf
     *   "- " satırı -> madde listesi
     *   "## " satırı -> ara başlık
     *
     * Girdi her zaman kaçışlanır; metin içinde HTML çalışmaz.
     */
    public static function zengin(?string $metin): HtmlString
    {
        $metin = trim((string) $metin);

        if ($metin === '') {
            return new HtmlString('');
        }

        $cikti    = '';
        $paragraf = [];
        $madde    = [];

        $paragrafKapat = function () use (&$cikti, &$paragraf): void {
            if (!$paragraf) return;
            $cikti .= '<p>' . nl2br(e(implode("\n", $paragraf))) . '</p>';
            $paragraf = [];
        };

        $maddeKapat = function () use (&$cikti, &$madde): void {
            if (!$madde) return;
            $cikti .= '<ul class="metin-liste">';
            foreach ($madde as $m) {
                $cikti .= '<li>' . e($m) . '</li>';
            }
            $cikti .= '</ul>';
            $madde = [];
        };

        foreach (preg_split('/\R/', $metin) as $satir) {
            $satir = rtrim($satir);

            if (preg_match('/^\s*##\s+(.*)$/u', $satir, $e)) {
                $maddeKapat();
                $paragrafKapat();
                $cikti .= '<h4 class="metin-baslik">' . e(trim($e[1])) . '</h4>';
            } elseif (preg_match('/^\s*[-*•]\s+(.*)$/u', $satir, $e)) {
                $paragrafKapat();
                $madde[] = trim($e[1]);
            } elseif (trim($satir) === '') {
                $maddeKapat();
                $paragrafKapat();
            } else {
                $maddeKapat();
                $paragraf[] = $satir;
            }
        }

        $maddeKapat();
        $paragrafKapat();

        return new HtmlString($cikti);
    }

    /**
     * Etiketli liste ayarlarını ayrıştırır (telefonlar, departman e-postaları).
     *   Etiket|değer        -> ['etiket'=>…, 'deger'=>…, 'bayrak'=>'']
     *   Etiket|değer|wa     -> bayrak 'wa' (WhatsApp bağlantısı)
     */
    public static function etiketliListe(?string $metin): array
    {
        $cikti = [];

        foreach (preg_split('/\R/', (string) $metin) as $satir) {
            $satir = trim($satir);
            if ($satir === '') continue;

            $parca = array_map('trim', explode('|', $satir));
            $deger = $parca[1] ?? '';
            if ($deger === '') continue;

            $cikti[] = [
                'etiket' => $parca[0],
                'deger'  => $deger,
                'bayrak' => mb_strtolower($parca[2] ?? ''),
            ];
        }

        return $cikti;
    }

    /** tel: / wa.me bağlantısı için yalnızca rakamlar. */
    public static function telRakam(?string $no): string
    {
        return preg_replace('/\D/', '', (string) $no);
    }

    /**
     * DB'de tutulan görsel yolunu adrese çevirir.
     * Tam adresler olduğu gibi bırakılır; proje içi göreli yollar
     * (img/partners/x.jpg) site köküne göre çözülür.
     */
    public static function gorselUrl(?string $yol): string
    {
        $yol = trim((string) $yol);

        if ($yol === '') {
            return '';
        }

        if (preg_match('#^(https?:)?//#i', $yol) || str_starts_with($yol, 'data:')) {
            return $yol;
        }

        return asset(ltrim($yol, '/'));
    }

    /** Panelden yüklenen logo, yoksa varsayılan dosya. */
    public static function logoUrl(): string
    {
        $yol = trim(\App\Models\Ayar::al('logo'));

        return self::gorselUrl($yol !== '' ? $yol : 'img/logo-fgg-256.png');
    }

    /** Tarihi aktif dile göre yazar. */
    public static function tarih(?string $tarih): string
    {
        if (!$tarih) {
            return '';
        }

        $zaman = strtotime($tarih);

        if (app()->getLocale() === 'en') {
            return date('d M Y', $zaman);
        }

        $aylar = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
                  'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];

        return date('d', $zaman) . ' ' . $aylar[(int) date('m', $zaman)] . ' ' . date('Y', $zaman);
    }

    /** Başlıktan URL parçası üretir (Türkçe karakterler sadeleşir). */
    public static function slug(?string $metin): string
    {
        $tr = ['ç','Ç','ğ','Ğ','ı','İ','ö','Ö','ş','Ş','ü','Ü'];
        $en = ['c','c','g','g','i','i','o','o','s','s','u','u'];

        $metin = str_replace($tr, $en, (string) $metin);
        $metin = mb_strtolower(trim($metin));
        $metin = preg_replace('/[^a-z0-9]+/', '-', $metin);

        return trim($metin, '-');
    }
}

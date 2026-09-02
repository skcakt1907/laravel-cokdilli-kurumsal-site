<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Panelden yüklenen görselden sekme simgesi (favicon) üretir.
 *
 * Ham dosyayı olduğu gibi kullanmak yerine üç boyut çıkarılır:
 * tarayıcı 500x300'lük bir PNG'yi 16px'e kendisi indirdiğinde sonuç
 * bulanık olur; burada kenar boşluğu kırpılıp kare tuvale oturtularak
 * her boyut ayrı ayrı örneklenir.
 *
 * GD eklentisi yoksa üretim yapılmaz, dosya olduğu gibi kullanılır —
 * paylaşımlı hostingde GD kapalı olabilir, yükleme yine de çalışsın.
 */
class SimgeUret
{
    /** Üretilecek kenar uzunlukları. */
    private const BOYUTLAR = [16, 32, 180];

    private const IZINLI_MIME = ['image/png', 'image/jpeg', 'image/webp'];

    /**
     * Yükler ve DB'ye yazılacak ANA yolu döndürür (ör. uploads/simge/abc.png).
     * Boyutlar aynı klasöre "-32", "-16" ekleriyle yazılır.
     *
     * Başarısızlıkta $mevcut olduğu gibi döner.
     */
    public static function calistir(?UploadedFile $dosya, ?string $mevcut = null): ?string
    {
        if (!$dosya || !$dosya->isValid()) {
            return $mevcut;
        }

        if (!in_array($dosya->getMimeType(), self::IZINLI_MIME, true)) {
            return $mevcut;
        }

        $klasor = public_path('uploads/simge');

        if (!is_dir($klasor) && !mkdir($klasor, 0755, true) && !is_dir($klasor)) {
            return $mevcut;
        }

        // Eski simge dosyalarını (ana + boyutlar) temizle
        self::sil($mevcut);

        $taban = 'simge-' . now()->format('YmdHis');
        $ana   = "{$klasor}/{$taban}.png";

        if (!extension_loaded('gd')) {
            // GD yok: dosyayı olduğu gibi koy, boyut üretme.
            $dosya->move($klasor, "{$taban}.png");

            return "uploads/simge/{$taban}.png";
        }

        $kaynak = self::ac($dosya->getPathname(), $dosya->getMimeType());

        if (!$kaynak) {
            return $mevcut;
        }

        $kare = self::kareYap($kaynak);
        imagedestroy($kaynak);

        imagepng($kare, $ana, 9);

        foreach (self::BOYUTLAR as $b) {
            $kucuk = self::olcekle($kare, $b);
            imagepng($kucuk, "{$klasor}/{$taban}-{$b}.png", 9);
            imagedestroy($kucuk);
        }

        imagedestroy($kare);

        return "uploads/simge/{$taban}.png";
    }

    /** Simge ve türevlerini siler. Yalnızca uploads/simge altına dokunur. */
    public static function sil(?string $yol): void
    {
        $yol = trim((string) $yol);

        if ($yol === '' || !str_starts_with($yol, 'uploads/simge/')) {
            return;
        }

        $tam = public_path($yol);

        if (str_contains($yol, '..')) {
            return;
        }

        $taban = preg_replace('/\.png$/', '', $tam);

        foreach (array_merge([$tam], array_map(fn ($b) => "{$taban}-{$b}.png", self::BOYUTLAR)) as $d) {
            if (is_file($d)) {
                @unlink($d);
            }
        }
    }

    /**
     * Verilen ana yola ait boyut dosyası varsa onun göreli yolunu,
     * yoksa ana yolu döndürür. Görünümler bunu kullanır.
     */
    public static function boyut(string $anaYol, int $b): string
    {
        $aday = preg_replace('/\.png$/', "-{$b}.png", $anaYol);

        return is_file(public_path($aday)) ? $aday : $anaYol;
    }

    // ---- iç yardımcılar -------------------------------------------------

    private static function ac(string $yol, string $mime): ?\GdImage
    {
        $im = match ($mime) {
            'image/png'  => @imagecreatefrompng($yol),
            'image/jpeg' => @imagecreatefromjpeg($yol),
            'image/webp' => @imagecreatefromwebp($yol),
            default      => null,
        };

        return $im ?: null;
    }

    /** Saydam kenarı kırpar, kare tuvale ortalar. */
    private static function kareYap(\GdImage $kaynak): \GdImage
    {
        imagealphablending($kaynak, false);
        imagesavealpha($kaynak, true);

        $g = imagesx($kaynak);
        $y = imagesy($kaynak);

        $solX = $g; $sagX = 0; $ustY = $y; $altY = 0;

        for ($i = 0; $i < $g; $i++) {
            for ($j = 0; $j < $y; $j++) {
                if (((imagecolorat($kaynak, $i, $j) >> 24) & 0x7F) < 110) {
                    if ($i < $solX) $solX = $i;
                    if ($i > $sagX) $sagX = $i;
                    if ($j < $ustY) $ustY = $j;
                    if ($j > $altY) $altY = $j;
                }
            }
        }

        // Tamamı saydamsa kırpma yapma
        if ($sagX < $solX || $altY < $ustY) {
            $solX = 0; $ustY = 0; $sagX = $g - 1; $altY = $y - 1;
        }

        $kirpG = $sagX - $solX + 1;
        $kirpY = $altY - $ustY + 1;
        $kenar = max($kirpG, $kirpY);
        $dolgu = (int) round($kenar * 0.06);
        $tuval = $kenar + $dolgu * 2;

        $kare = self::bosTuval($tuval);
        imagecopy(
            $kare, $kaynak,
            $dolgu + (int) (($kenar - $kirpG) / 2),
            $dolgu + (int) (($kenar - $kirpY) / 2),
            $solX, $ustY, $kirpG, $kirpY
        );

        return $kare;
    }

    private static function olcekle(\GdImage $kare, int $b): \GdImage
    {
        $k = self::bosTuval($b);
        imagecopyresampled($k, $kare, 0, 0, 0, 0, $b, $b, imagesx($kare), imagesy($kare));

        return $k;
    }

    private static function bosTuval(int $b): \GdImage
    {
        $im = imagecreatetruecolor($b, $b);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        imagealphablending($im, true);

        return $im;
    }
}

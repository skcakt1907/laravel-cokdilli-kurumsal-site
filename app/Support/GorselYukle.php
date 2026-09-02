<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Görsel yükleme.
 *
 * Düz PHP sürümünün sertleştirmeleri korundu:
 *   - uzantı istemciden DEĞİL guessExtension()'dan türetilir
 *   - mime beyaz listesi (svg yok — içine script gömülebiliyor)
 *   - dosya adı rastgele; yüklenen ad hiç kullanılmaz
 *
 * Dosyalar public/uploads altına gider; storage symlink'e gerek yok
 * (paylaşımlı hosting dostu).
 */
class GorselYukle
{
    private const IZINLI_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    /**
     * Yükler ve DB'ye yazılacak göreli yolu döndürür.
     * Dosya gelmemişse $mevcut olduğu gibi geri döner.
     */
    public static function calistir(?UploadedFile $dosya, string $klasor, ?string $mevcut = null): ?string
    {
        if (!$dosya || !$dosya->isValid()) {
            return $mevcut;
        }

        $mime = $dosya->getMimeType();

        if (!isset(self::IZINLI_MIME[$mime])) {
            return $mevcut;   // izinsiz tür — sessizce yok sayılır
        }

        $uzanti = self::IZINLI_MIME[$mime];
        $ad     = Str::random(16) . '-' . now()->format('YmdHis') . '.' . $uzanti;
        $hedef  = public_path('uploads/' . $klasor);

        if (!is_dir($hedef)) {
            mkdir($hedef, 0755, true);
        }

        $dosya->move($hedef, $ad);

        // Yeni dosya yerleştiyse eskisini temizle
        self::sil($mevcut);

        return 'uploads/' . $klasor . '/' . $ad;
    }

    /** Eski görseli siler. Yalnızca uploads/ altındaki yollara dokunur. */
    public static function sil(?string $yol): void
    {
        $yol = trim((string) $yol);

        if ($yol === '' || !str_starts_with($yol, 'uploads/')) {
            return;
        }

        // Dizin dışına çıkma denemesine karşı
        if (str_contains($yol, '..')) {
            return;
        }

        $tam = public_path($yol);

        if (is_file($tam)) {
            @unlink($tam);
        }
    }
}

<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Video alanı — tek bir metin sütununda iki şey saklanabilir:
 *
 *   1) Dış bağlantı  : https://youtu.be/xxxx  (YouTube / Vimeo)
 *   2) Yüklenen dosya: uploads/video/abc-20260816.mp4
 *
 * Hangisi olduğu Medya::video() ile ayrıştırılır; panelde tek alan görünür.
 *
 * ESKİYİ SİLME: yeni bir dosya yüklendiğinde ya da yerine bağlantı
 * girildiğinde önceki DOSYA diskten silinir — sunucu eski videolarla
 * dolmasın. Dış bağlantıda silinecek dosya olmadığı için bir şey yapılmaz.
 *
 * Sertleştirme GorselYukle ile aynı: uzantı istemciden değil mime'dan
 * türetilir, dosya adı rastgeledir, silme yalnızca uploads/video altına
 * dokunur.
 */
class VideoYukle
{
    private const IZINLI_MIME = [
        'video/mp4'       => 'mp4',
        'video/webm'      => 'webm',
        'video/ogg'       => 'ogv',
        'video/quicktime' => 'mp4',   // .mov — mp4 kabı olarak servis edilir
    ];

    private const KLASOR = 'uploads/video';

    /**
     * Kaydedilecek değeri döndürür.
     *
     * @param  UploadedFile|null $dosya  Yüklenen dosya (varsa)
     * @param  string|null       $link   Girilen dış bağlantı (varsa)
     * @param  string|null       $mevcut DB'deki şu anki değer
     * @param  bool              $sil    "Videoyu kaldır" işaretlendi mi
     */
    public static function calistir(
        ?UploadedFile $dosya,
        ?string $link,
        ?string $mevcut = null,
        bool $sil = false
    ): ?string {
        $mevcut = trim((string) $mevcut) ?: null;

        if ($sil) {
            self::sil($mevcut);

            return null;
        }

        // 1) Dosya yüklendiyse bağlantıdan önce gelir
        if ($dosya && $dosya->isValid()) {
            $mime = $dosya->getMimeType();

            if (!isset(self::IZINLI_MIME[$mime])) {
                return $mevcut;   // izinsiz tür — sessizce yok sayılır
            }

            $hedef = public_path(self::KLASOR);

            if (!is_dir($hedef) && !mkdir($hedef, 0755, true) && !is_dir($hedef)) {
                return $mevcut;
            }

            $ad = Str::random(12) . '-' . now()->format('YmdHis') . '.' . self::IZINLI_MIME[$mime];
            $dosya->move($hedef, $ad);

            self::sil($mevcut);   // eskisini temizle

            return self::KLASOR . '/' . $ad;
        }

        // 2) Bağlantı girildiyse
        $link = trim((string) $link);

        if ($link !== '') {
            if ($link !== $mevcut) {
                self::sil($mevcut);   // dosyadan bağlantıya geçildiyse dosya gitsin
            }

            return $link;
        }

        // 3) Ne dosya ne bağlantı — mevcut korunur
        return $mevcut;
    }

    /** Yüklenmiş videoyu siler. Dış bağlantılara ve klasör dışına dokunmaz. */
    public static function sil(?string $deger): void
    {
        $deger = trim((string) $deger);

        if ($deger === '' || !str_starts_with($deger, self::KLASOR . '/') || str_contains($deger, '..')) {
            return;
        }

        $tam = public_path($deger);

        if (is_file($tam)) {
            @unlink($tam);
        }
    }

    /** Sunucunun izin verdiği en büyük yükleme boyutu (MB). */
    public static function sinirMb(): int
    {
        $cevir = function (string $d): int {
            $d = trim($d);
            $son = strtolower(substr($d, -1));
            $sayi = (int) $d;

            return match ($son) {
                'g'     => $sayi * 1024,
                'm'     => $sayi,
                'k'     => (int) ($sayi / 1024),
                default => (int) ($sayi / 1048576),
            };
        };

        return max(1, min(
            $cevir((string) ini_get('upload_max_filesize')),
            $cevir((string) ini_get('post_max_size'))
        ));
    }
}

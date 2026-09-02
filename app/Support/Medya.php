<?php

namespace App\Support;

/**
 * Video alanındaki değeri oynatılabilir bir şeye çevirir.
 *
 * Panelde tek bir alan var; içinde YouTube linki de olabilir, yüklenmiş
 * bir dosya yolu da. Görünümler tür ayrımını burada yapılmış hâliyle alır.
 */
class Medya
{
    /**
     * @return array{tip:string, kaynak:string}|null
     *         tip: youtube | vimeo | dosya
     */
    public static function video(?string $deger): ?array
    {
        $deger = trim((string) $deger);

        if ($deger === '') {
            return null;
        }

        // --- YouTube --------------------------------------------------
        // watch?v=ID, youtu.be/ID, /embed/ID, /shorts/ID
        if (preg_match(
            '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{6,})~i',
            $deger,
            $e
        )) {
            // rel=0: bitince başka kanalların videosunu önermesin
            // modestbranding: köşedeki YouTube logosunu küçültür
            return [
                'tip'    => 'youtube',
                'kaynak' => 'https://www.youtube-nocookie.com/embed/' . $e[1] . '?rel=0&modestbranding=1',
            ];
        }

        // --- Vimeo ----------------------------------------------------
        // vimeo.com/123456789  ya da  player.vimeo.com/video/123456789
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $deger, $e)) {
            return [
                'tip'    => 'vimeo',
                'kaynak' => 'https://player.vimeo.com/video/' . $e[1],
            ];
        }

        // --- Yüklenmiş dosya ------------------------------------------
        if (str_starts_with($deger, 'uploads/')) {
            return ['tip' => 'dosya', 'kaynak' => asset($deger)];
        }

        // --- Tanınmayan dış bağlantı: gömme denenmez ------------------
        if (preg_match('~^https?://~i', $deger)) {
            return ['tip' => 'baglanti', 'kaynak' => $deger];
        }

        return null;
    }
}

<?php

namespace App\Support;

/**
 * Dil listesine tek kapıdan erişim.
 *
 * config/diller.php okunur; kod hiçbir yerde dil kodlarını sabit
 * yazmaz. Yeni dil açmak = config'de bir satır.
 */
class Dil
{
    /** Çevirisi tutulmayan, tabloların kendi sütunlarında duran dil. */
    public static function temel(): string
    {
        return (string) config('diller.temel', 'tr');
    }

    /** @return array<string, array> aktif diller: kod => tanım */
    public static function aktif(): array
    {
        return array_filter(
            (array) config('diller.liste', []),
            fn ($d) => ($d['aktif'] ?? false) === true
        );
    }

    /** @return array<int, string> aktif dil kodları */
    public static function kodlar(): array
    {
        return array_keys(self::aktif());
    }

    /**
     * Temel dil dışındaki aktif diller — çeviri alanı gereken diller.
     *
     * @return array<string, array>
     */
    public static function cevrilecek(): array
    {
        return array_diff_key(self::aktif(), [self::temel() => true]);
    }

    public static function gecerliMi(?string $kod): bool
    {
        return is_string($kod) && in_array($kod, self::kodlar(), true);
    }

    public static function ad(string $kod): string
    {
        return (string) (config("diller.liste.{$kod}.ad") ?? strtoupper($kod));
    }

    public static function yerelAd(string $kod): string
    {
        return (string) (config("diller.liste.{$kod}.yerel") ?? self::ad($kod));
    }

    public static function yon(?string $kod = null): string
    {
        $kod = $kod ?: app()->getLocale();

        return config("diller.liste.{$kod}.yon") === 'rtl' ? 'rtl' : 'ltr';
    }

    public static function sagdanSolaMi(?string $kod = null): bool
    {
        return self::yon($kod) === 'rtl';
    }
}

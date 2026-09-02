<?php

/*
|--------------------------------------------------------------------------
| Site dilleri
|--------------------------------------------------------------------------
|
| Yeni bir dil açmak için buradaki 'aktif' değerini true yapmak yeterli:
| panelde o dilin alanları belirir, site menüsüne eklenir, çeviriler
| `ceviriler` tablosunda tutulur. Şema değişmez, kolon eklenmez.
|
| 'temel' dilin çevirisi tutulmaz — o, tabloların kendi sütunlarıdır.
| Bir alanın çevirisi yoksa temel dile düşülür (fallback).
|
| 'yon' => 'rtl' olan dillerde sayfa düzeni sağdan sola çevrilir.
|
*/

return [

    'temel' => 'tr',

    'liste' => [
        'tr' => ['ad' => 'Türkçe',  'yerel' => 'Türkçe',   'yon' => 'ltr', 'aktif' => true],
        'en' => ['ad' => 'English', 'yerel' => 'English',  'yon' => 'ltr', 'aktif' => true],

        // Müşteri talebi: Arapça ve Farsça. İçerik hazır olunca aktif => true.
        'ar' => ['ad' => 'Arapça',  'yerel' => 'العربية',  'yon' => 'rtl', 'aktif' => true],
        'fa' => ['ad' => 'Farsça',  'yerel' => 'فارسی',    'yon' => 'rtl', 'aktif' => true],
    ],

];

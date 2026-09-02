<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Çeviriler tek tabloda.
 *
 * ÖNCESİ: her metin alanının bir de `_en` sütunu vardı. İki dil için
 * çalışıyordu ama Arapça + Farsça eklenince 17 alan × 2 dil = 34 sütun
 * daha açmak gerekecekti; her yeni dil şemayı büyütüyordu.
 *
 * SONRASI: çeviriler satır olarak tutulur. Yeni dil = sıfır şema
 * değişikliği, sadece config/diller.php'de bir satır.
 *
 * ESKİ SÜTUNLAR SİLİNMİYOR. DilliAlan::d() önce bu tabloya bakar,
 * bulamazsa eski `_en` sütununa düşer. Böylece taşımada bir şey
 * atlanmışsa site yine doğru içeriği gösterir. Sütunlar bir süre
 * sorunsuz çalıştıktan sonra ayrı bir göçle kaldırılabilir.
 */
return new class extends Migration
{
    /** Ayarlar tablosunun kayıt kimliği yok; hepsi 0 altında toplanır. */
    private const AYAR_ID = 0;

    public function up(): void
    {
        Schema::create('ceviriler', function (Blueprint $t) {
            $t->id();
            $t->string('tablo', 60);            // hizmetler, projeler, ayarlar...
            $t->unsignedBigInteger('kayit_id'); // ayarlar için 0
            $t->string('alan', 60);             // baslik, ozet, icerik...
            $t->string('dil', 5);               // en, ar, fa
            $t->text('deger')->nullable();
            $t->timestamps();

            // Aynı alanın aynı dilde iki çevirisi olamaz.
            $t->unique(['tablo', 'kayit_id', 'alan', 'dil'], 'ceviri_tekil');
            $t->index(['dil', 'tablo']);
        });

        $this->mevcutVeriyiTasi();
    }

    public function down(): void
    {
        Schema::dropIfExists('ceviriler');
    }

    /** Var olan `_en` sütunlarını ve ayarları yeni tabloya kopyalar. */
    private function mevcutVeriyiTasi(): void
    {
        $simdi = now();
        $satir = [];

        // --- İçerik tabloları: *_en sütunları -------------------------
        foreach (DB::select('SHOW TABLES') as $kayit) {
            $tablo = array_values((array) $kayit)[0];

            if ($tablo === 'ceviriler' || $tablo === 'ayarlar') {
                continue;
            }

            $kolonlar = array_column(DB::select("SHOW COLUMNS FROM `{$tablo}`"), 'Field');

            $ciftler = [];
            foreach ($kolonlar as $k) {
                if (str_ends_with($k, '_en') && in_array(substr($k, 0, -3), $kolonlar, true)) {
                    $ciftler[substr($k, 0, -3)] = $k;
                }
            }

            if (!$ciftler || !in_array('id', $kolonlar, true)) {
                continue;
            }

            foreach (DB::table($tablo)->get() as $kyt) {
                foreach ($ciftler as $temel => $enKolon) {
                    $deger = trim((string) ($kyt->{$enKolon} ?? ''));

                    if ($deger === '') {
                        continue;
                    }

                    $satir[] = [
                        'tablo'      => $tablo,
                        'kayit_id'   => $kyt->id,
                        'alan'       => $temel,
                        'dil'        => 'en',
                        'deger'      => $deger,
                        'created_at' => $simdi,
                        'updated_at' => $simdi,
                    ];
                }
            }
        }

        // --- Ayarlar: anahtar_en satırları ---------------------------
        if (Schema::hasTable('ayarlar')) {
            foreach (DB::table('ayarlar')->get() as $ayar) {
                if (!str_ends_with($ayar->anahtar, '_en')) {
                    continue;
                }

                $deger = trim((string) $ayar->deger);

                if ($deger === '') {
                    continue;
                }

                $satir[] = [
                    'tablo'      => 'ayarlar',
                    'kayit_id'   => self::AYAR_ID,
                    'alan'       => substr($ayar->anahtar, 0, -3),
                    'dil'        => 'en',
                    'deger'      => $deger,
                    'created_at' => $simdi,
                    'updated_at' => $simdi,
                ];
            }
        }

        foreach (array_chunk($satir, 200) as $parca) {
            DB::table('ceviriler')->insertOrIgnore($parca);
        }
    }
};

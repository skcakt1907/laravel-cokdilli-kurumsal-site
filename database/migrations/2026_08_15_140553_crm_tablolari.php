<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CRM çekirdeği. Yapı İş Ortağım'ın CRM'i örnek alınarak kuruldu,
 * tablo ve kolon adları oradakiyle aynı tutuldu ki iki proje arasında
 * geçiş yapan biri kaybolmasın.
 *
 * TASARIM NOTU — "alanları sonradan belirleriz":
 * Sık kullanılacağı belli olan alanlar (ad, e-posta, telefon, tutar)
 * gerçek kolon — arama ve sıralama hızlı olsun diye. Sonradan çıkacak
 * alanlar için crm_alan_tanimlari / crm_alan_degerleri ikilisi var;
 * yeni alan eklemek ALTER TABLE değil, panelden bir satır.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---- Satış hattı ve aşamalar ----------------------------------
        Schema::create('crm_pipelines', function (Blueprint $t) {
            $t->id();
            $t->string('adi', 150);
            $t->string('aciklama', 255)->nullable();
            $t->integer('sira')->default(0);
            $t->boolean('varsayilan')->default(false);
            $t->timestamps();
        });

        Schema::create('crm_stages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pipeline_id')->constrained('crm_pipelines')->cascadeOnDelete();
            $t->string('adi', 150);
            $t->unsignedTinyInteger('olasilik')->default(0);   // kazanma ihtimali %
            $t->string('renk', 20)->nullable();
            $t->integer('sira')->default(0);
            $t->enum('sonuc', ['acik', 'kazanildi', 'kaybedildi'])->default('acik');
            $t->timestamps();
        });

        // ---- Müşteriler ------------------------------------------------
        Schema::create('crm_customers', function (Blueprint $t) {
            $t->id();
            $t->string('adi', 150);
            $t->string('unvan', 150)->nullable();
            $t->string('email', 150)->nullable();
            $t->string('telefon', 50)->nullable();
            $t->string('sektor', 100)->nullable();
            $t->string('kaynak', 100)->nullable();     // web formu, referans, fuar...
            $t->string('ulke', 80)->nullable();
            $t->text('adres')->nullable();
            $t->string('durum', 50)->default('aday');
            $t->foreignId('sorumlu_id')->nullable()->constrained('admin')->nullOnDelete();
            $t->unsignedBigInteger('mesaj_id')->nullable();   // hangi iletişim mesajından geldi
            $t->json('etiketler')->nullable();
            $t->timestamps();

            $t->index('durum');
            $t->index('email');
        });

        // ---- Fırsatlar --------------------------------------------------
        Schema::create('crm_opportunities', function (Blueprint $t) {
            $t->id();
            $t->foreignId('musteri_id')->constrained('crm_customers')->cascadeOnDelete();
            $t->foreignId('pipeline_id')->nullable()->constrained('crm_pipelines')->nullOnDelete();
            $t->foreignId('stage_id')->nullable()->constrained('crm_stages')->nullOnDelete();
            $t->string('baslik', 180);
            $t->decimal('tutar', 14, 2)->default(0);
            $t->string('para_birimi', 10)->default('USD');
            $t->enum('durum', ['acik', 'kazanildi', 'kaybedildi'])->default('acik');
            $t->date('kapanis_tarihi')->nullable();
            $t->foreignId('sorumlu_id')->nullable()->constrained('admin')->nullOnDelete();
            $t->text('aciklama')->nullable();
            $t->timestamps();

            $t->index('durum');
        });

        // ---- Notlar ve görevler ----------------------------------------
        Schema::create('crm_notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('musteri_id')->nullable()->constrained('crm_customers')->cascadeOnDelete();
            $t->foreignId('firsat_id')->nullable()->constrained('crm_opportunities')->cascadeOnDelete();
            $t->foreignId('yazan_id')->nullable()->constrained('admin')->nullOnDelete();
            $t->text('icerik');
            $t->timestamps();
        });

        Schema::create('crm_tasks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('musteri_id')->nullable()->constrained('crm_customers')->cascadeOnDelete();
            $t->foreignId('firsat_id')->nullable()->constrained('crm_opportunities')->cascadeOnDelete();
            $t->string('baslik', 200);
            $t->text('aciklama')->nullable();
            $t->date('son_tarih')->nullable();
            $t->enum('durum', ['bekliyor', 'tamamlandi', 'iptal'])->default('bekliyor');
            $t->foreignId('sorumlu_id')->nullable()->constrained('admin')->nullOnDelete();
            $t->timestamps();

            $t->index('durum');
        });

        // ---- Panelden tanımlanan alanlar --------------------------------
        Schema::create('crm_alan_tanimlari', function (Blueprint $t) {
            $t->id();
            $t->enum('tablo', ['musteri', 'firsat'])->default('musteri');
            $t->string('anahtar', 60);
            $t->string('etiket', 150);
            $t->string('etiket_en', 150)->nullable();
            $t->enum('tip', ['metin', 'uzun_metin', 'sayi', 'tarih', 'secim', 'onay'])->default('metin');
            $t->text('secenekler')->nullable();   // 'secim' tipinde her satır bir seçenek
            $t->boolean('zorunlu')->default(false);
            $t->integer('sira')->default(0);
            $t->boolean('durum')->default(true);
            $t->timestamps();

            $t->unique(['tablo', 'anahtar']);
        });

        Schema::create('crm_alan_degerleri', function (Blueprint $t) {
            $t->id();
            $t->enum('tablo', ['musteri', 'firsat']);
            $t->unsignedBigInteger('kayit_id');
            $t->string('anahtar', 60);
            $t->text('deger')->nullable();

            $t->unique(['tablo', 'kayit_id', 'anahtar']);
            $t->index(['tablo', 'kayit_id']);
        });

        $this->baslangicVerisi();
    }

    /** Varsayılan satış hattı ve aşamaları. */
    private function baslangicVerisi(): void
    {
        $simdi = now();

        DB::table('crm_pipelines')->insert([
            'id'         => 1,
            'adi'        => 'Genel Satış Hattı',
            'aciklama'   => 'Web sitesinden ve doğrudan gelen taleplerin izlendiği varsayılan hat',
            'sira'       => 1,
            'varsayilan' => true,
            'created_at' => $simdi,
            'updated_at' => $simdi,
        ]);

        $asamalar = [
            ['Yeni Talep',       10, '#8a8a8a', 'acik'],
            ['İlk Temas',        25, '#d4af37', 'acik'],
            ['Görüşme / Analiz', 50, '#e8c65a', 'acik'],
            ['Teklif Verildi',   75, '#c9a227', 'acik'],
            ['Kazanıldı',       100, '#3fa34d', 'kazanildi'],
            ['Kaybedildi',        0, '#b4453f', 'kaybedildi'],
        ];

        foreach ($asamalar as $sira => [$adi, $olasilik, $renk, $sonuc]) {
            DB::table('crm_stages')->insert([
                'pipeline_id' => 1,
                'adi'         => $adi,
                'olasilik'    => $olasilik,
                'renk'        => $renk,
                'sira'        => $sira + 1,
                'sonuc'       => $sonuc,
                'created_at'  => $simdi,
                'updated_at'  => $simdi,
            ]);
        }
    }

    public function down(): void
    {
        foreach ([
            'crm_alan_degerleri', 'crm_alan_tanimlari', 'crm_tasks',
            'crm_notes', 'crm_opportunities', 'crm_customers',
            'crm_stages', 'crm_pipelines',
        ] as $tablo) {
            Schema::dropIfExists($tablo);
        }
    }
};

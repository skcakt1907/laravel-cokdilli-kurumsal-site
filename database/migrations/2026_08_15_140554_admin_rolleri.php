<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mevcut `admin` tablosu tek kullanıcılıydı (kullanici / sifre_hash / ad_soyad).
 * CRM'de kayıtlara sorumlu atanacağı için çok kullanıcıya geçiliyor.
 *
 * Tablo düz PHP sürümünden devralındı; yalnızca kolon ekleniyor, mevcut
 * kolonlara dokunulmuyor — böylece eski sürüm de aynı tabloyla çalışmaya
 * devam edebilir (geçiş döneminde ikisi yan yana durabilsin diye).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin', function (Blueprint $tablo) {
            if (!Schema::hasColumn('admin', 'rol')) {
                $tablo->enum('rol', ['sahip', 'yonetici', 'temsilci'])
                      ->default('temsilci')->after('ad_soyad');
            }
            if (!Schema::hasColumn('admin', 'eposta')) {
                $tablo->string('eposta', 150)->nullable()->after('rol');
            }
            if (!Schema::hasColumn('admin', 'telefon')) {
                $tablo->string('telefon', 50)->nullable()->after('eposta');
            }
            if (!Schema::hasColumn('admin', 'durum')) {
                $tablo->boolean('durum')->default(true)->after('telefon');
            }
            if (!Schema::hasColumn('admin', 'son_giris')) {
                $tablo->dateTime('son_giris')->nullable()->after('durum');
            }
            if (!Schema::hasColumn('admin', 'remember_token')) {
                $tablo->rememberToken()->after('son_giris');
            }
        });

        // Kurulumdaki tek hesap sistemin sahibi olsun.
        DB::table('admin')->where('kullanici', 'admin')->update(['rol' => 'sahip']);
    }

    public function down(): void
    {
        Schema::table('admin', function (Blueprint $tablo) {
            $tablo->dropColumn(['rol', 'eposta', 'telefon', 'durum', 'son_giris', 'remember_token']);
        });
    }
};

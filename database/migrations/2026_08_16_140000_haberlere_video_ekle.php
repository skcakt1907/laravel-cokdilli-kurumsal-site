<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Haberlere video alanı.
 *
 * Tek sütun; içinde ya dış bağlantı (YouTube/Vimeo) ya da yüklenmiş
 * dosyanın yolu durur. Ayrımı App\Support\Medya::video() yapar —
 * "tip" diye ikinci bir sütun tutmaya gerek yok, değerin kendisi
 * hangisi olduğunu zaten söylüyor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog', function (Blueprint $t) {
            if (!Schema::hasColumn('blog', 'video')) {
                $t->string('video', 255)->nullable()->after('gorsel');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog', function (Blueprint $t) {
            $t->dropColumn('video');
        });
    }
};

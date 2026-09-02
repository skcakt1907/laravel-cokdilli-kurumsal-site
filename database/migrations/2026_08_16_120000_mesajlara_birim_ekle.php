<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * İletişim mesajı hangi sektör ailesiyle ilgiliyse o saklanır.
 * Bildirim e-postası bu değere göre ilgili birime yönlendirilir
 * (ayarlar > grup_mailler eşleşmesi).
 *
 * Sektör adı serbest metin olarak tutuluyor, yabancı anahtar değil:
 * panelden bir sektör ailesinin adı değişse bile eski mesajın hangi
 * birime gittiği kaydı bozulmasın.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesajlar', function (Blueprint $t) {
            $t->string('birim', 120)->nullable()->after('konu');
            $t->string('gonderildi', 150)->nullable()->after('birim');   // bildirim hangi adrese gitti
        });
    }

    public function down(): void
    {
        Schema::table('mesajlar', function (Blueprint $t) {
            $t->dropColumn(['birim', 'gonderildi']);
        });
    }
};

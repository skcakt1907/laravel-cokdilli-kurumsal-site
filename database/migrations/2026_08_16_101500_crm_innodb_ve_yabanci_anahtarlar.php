<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CRM tabloları MyISAM olarak oluşmuştu: sunucunun varsayılan motoru MyISAM
 * ve config/database.php'de 'engine' => null bırakılmıştı. MyISAM yabancı
 * anahtarları HATA VERMEDEN yok sayar; bu yüzden crm_tablolari göçünde
 * tanımlanan cascade kuralları hiç oluşmamıştı. Sonuç: müşteri silinince
 * notları/fırsatları kalıyor, üstelik işlem (transaction) desteği de yok.
 *
 * Ayrıca `admin.id` int (işaretli), CRM'deki sorumlu_id/yazan_id ise
 * bigint unsigned. InnoDB'ye geçilse bile bu tip farkı yüzünden admin'e
 * giden yabancı anahtarlar kurulamazdı; admin.id burada genişletiliyor.
 *
 * config/database.php'de engine artık InnoDB'ye sabitlendi — bundan sonraki
 * göçler doğru motorla oluşur.
 */
return new class extends Migration
{
    /** Yabancı anahtarlar: [tablo, kolon, hedef tablo, silinince] */
    private array $anahtarlar = [
        ['crm_stages',        'pipeline_id', 'crm_pipelines',     'CASCADE'],
        ['crm_customers',     'sorumlu_id',  'admin',             'SET NULL'],
        ['crm_opportunities', 'musteri_id',  'crm_customers',     'CASCADE'],
        ['crm_opportunities', 'pipeline_id', 'crm_pipelines',     'SET NULL'],
        ['crm_opportunities', 'stage_id',    'crm_stages',        'SET NULL'],
        ['crm_opportunities', 'sorumlu_id',  'admin',             'SET NULL'],
        ['crm_notes',         'musteri_id',  'crm_customers',     'CASCADE'],
        ['crm_notes',         'firsat_id',   'crm_opportunities', 'CASCADE'],
        ['crm_notes',         'yazan_id',    'admin',             'SET NULL'],
        ['crm_tasks',         'musteri_id',  'crm_customers',     'CASCADE'],
        ['crm_tasks',         'firsat_id',   'crm_opportunities', 'CASCADE'],
        ['crm_tasks',         'sorumlu_id',  'admin',             'SET NULL'],
    ];

    public function up(): void
    {
        // 1) Motoru InnoDB'ye çevir.
        foreach ($this->crmTablolari() as $tablo) {
            DB::statement("ALTER TABLE `{$tablo}` ENGINE = InnoDB");
        }

        // 2) Öksüz kayıtları temizle — yabancı anahtar bunlar dururken kurulmaz.
        $this->oksuzleriTemizle();

        // 3) admin.id'yi bigint unsigned yap ki CRM kolonlarıyla eşleşsin.
        //    Genişletme; mevcut değerler aynen korunur.
        if ($this->kolonTipi('admin', 'id') !== 'bigint unsigned') {
            DB::statement('ALTER TABLE `admin` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }

        // 4) Yabancı anahtarları kur.
        foreach ($this->anahtarlar as [$tablo, $kolon, $hedef, $davranis]) {
            if ($this->anahtarVar($tablo, $kolon)) {
                continue;
            }

            DB::statement(sprintf(
                'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`id`) ON DELETE %s',
                $tablo,
                $this->anahtarAdi($tablo, $kolon),
                $kolon,
                $hedef,
                $davranis
            ));
        }
    }

    public function down(): void
    {
        foreach ($this->anahtarlar as [$tablo, $kolon]) {
            if ($this->anahtarVar($tablo, $kolon)) {
                DB::statement(sprintf(
                    'ALTER TABLE `%s` DROP FOREIGN KEY `%s`',
                    $tablo,
                    $this->anahtarAdi($tablo, $kolon)
                ));
            }
        }

        // Motor ve admin.id tipi geri alınmıyor: ikisi de düzeltme niteliğinde,
        // eski hâline döndürmek veri bütünlüğünü yeniden bozardı.
    }

    // ---- Yardımcılar ----------------------------------------------------

    private function crmTablolari(): array
    {
        $db = DB::getDatabaseName();

        return array_column(DB::select(
            "SELECT TABLE_NAME AS t FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = ? AND ENGINE = 'MyISAM'",
            [$db]
        ), 't');
    }

    private function oksuzleriTemizle(): void
    {
        // Fırsatlar önce: notlar/görevler fırsata da bağlı olabilir.
        DB::statement('DELETE o FROM crm_opportunities o
                       LEFT JOIN crm_customers c ON c.id = o.musteri_id
                       WHERE c.id IS NULL');

        foreach (['crm_notes', 'crm_tasks'] as $tablo) {
            DB::statement("DELETE n FROM {$tablo} n
                           LEFT JOIN crm_customers c ON c.id = n.musteri_id
                           WHERE n.musteri_id IS NOT NULL AND c.id IS NULL");

            DB::statement("DELETE n FROM {$tablo} n
                           LEFT JOIN crm_opportunities o ON o.id = n.firsat_id
                           WHERE n.firsat_id IS NOT NULL AND o.id IS NULL");
        }

        // Var olmayan panel kullanıcısına bakan sorumlu/yazan alanlarını boşalt.
        foreach ([['crm_customers','sorumlu_id'], ['crm_opportunities','sorumlu_id'],
                  ['crm_tasks','sorumlu_id'], ['crm_notes','yazan_id']] as [$tablo, $kolon]) {
            DB::statement("UPDATE {$tablo} n
                           LEFT JOIN admin a ON a.id = n.{$kolon}
                           SET n.{$kolon} = NULL
                           WHERE n.{$kolon} IS NOT NULL AND a.id IS NULL");
        }

        // Aşaması silinmiş pipeline'a bakan satırlar
        DB::statement('DELETE s FROM crm_stages s
                       LEFT JOIN crm_pipelines p ON p.id = s.pipeline_id
                       WHERE p.id IS NULL');
    }

    private function anahtarAdi(string $tablo, string $kolon): string
    {
        return "{$tablo}_{$kolon}_foreign";
    }

    private function anahtarVar(string $tablo, string $kolon): bool
    {
        return (bool) DB::select(
            'SELECT 1 FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [DB::getDatabaseName(), $tablo, $kolon]
        );
    }

    private function kolonTipi(string $tablo, string $kolon): string
    {
        // SHOW COLUMNS ... LIKE yer tutucu kabul etmez; information_schema kullanılıyor.
        $r = DB::select(
            'SELECT COLUMN_TYPE AS tip FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [DB::getDatabaseName(), $tablo, $kolon]
        );

        return $r ? strtolower($r[0]->tip) : '';
    }
};

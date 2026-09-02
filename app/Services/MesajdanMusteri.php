<?php

namespace App\Services;

use App\Models\CrmMusteri;
use App\Models\CrmNot;
use App\Models\Mesaj;
use Illuminate\Support\Facades\DB;

/**
 * Siteden gelen iletişim mesajını CRM'e aday müşteri olarak düşürür.
 *
 * Kural: aynı e-posta tekrar yazarsa YENİ kayıt açılmaz — mevcut müşteriye
 * not olarak eklenir. Aksi hâlde ısrarlı bir talep sahibi CRM'de onlarca
 * kopya kayıt oluştururdu.
 */
class MesajdanMusteri
{
    public function calistir(Mesaj $mesaj): CrmMusteri
    {
        return DB::transaction(function () use ($mesaj) {
            $musteri = $this->mevcuduBul($mesaj);

            if ($musteri) {
                $this->notDus($musteri, $mesaj, tekrar: true);

                // Kapanmış bir aday yeniden yazdıysa listeye geri gelsin.
                if ($musteri->durum === 'pasif') {
                    $musteri->update(['durum' => 'aday']);
                }

                return $musteri;
            }

            $musteri = new CrmMusteri([
                'adi'      => $mesaj->ad,
                'email'    => $mesaj->mail,
                'telefon'  => $mesaj->tel,
                'kaynak'   => 'web formu',
                'durum'    => 'aday',
                'mesaj_id' => $mesaj->id,
            ]);
            $musteri->save();

            $this->notDus($musteri, $mesaj, tekrar: false);

            return $musteri;
        });
    }

    /**
     * Aynı kişiyi e-posta üzerinden eşler. E-posta boşsa telefona bakar;
     * ikisi de yoksa eşleme yapılmaz (yeni kayıt açılır).
     */
    private function mevcuduBul(Mesaj $mesaj): ?CrmMusteri
    {
        if ($mesaj->mail) {
            $bulunan = CrmMusteri::whereRaw('LOWER(email) = ?', [mb_strtolower($mesaj->mail)])->first();
            if ($bulunan) {
                return $bulunan;
            }
        }

        if ($mesaj->tel) {
            // Biçim farklarını yok say: yalnızca rakamlara bak.
            $rakam = preg_replace('/\D/', '', $mesaj->tel);

            if (mb_strlen($rakam) >= 7) {
                return CrmMusteri::whereRaw(
                    "REGEXP_REPLACE(COALESCE(telefon,''), '[^0-9]', '') = ?",
                    [$rakam],
                )->first();
            }
        }

        return null;
    }

    private function notDus(CrmMusteri $musteri, Mesaj $mesaj, bool $tekrar): void
    {
        $basliklar = array_filter([
            $tekrar ? 'Aynı kişiden yeni mesaj' : 'Web formundan ilk temas',
            $mesaj->konu ? 'Konu: ' . $mesaj->konu : null,
        ]);

        $not = new CrmNot([
            'musteri_id' => $musteri->id,
            'icerik'     => implode("\n", $basliklar) . "\n\n" . $mesaj->mesaj,
        ]);

        // yazan_id boş: kayıt ziyaretçiden geldi, bir panel kullanıcısı yazmadı.
        $not->save();
    }
}

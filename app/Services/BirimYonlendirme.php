<?php

namespace App\Services;

use App\Models\Ayar;
use App\Models\Hizmet;
use App\Support\Metin;

/**
 * İletişim mesajını ilgili birimin e-postasına yönlendirir.
 *
 * Eşleşme panelden yönetilir: Ayarlar > grup_mailler, her satır
 * "Sektör Ailesi|adres@ornek-holding.com" biçiminde. Yeni sektör ailesi
 * eklendiğinde kod değişmez, o listeye bir satır eklenir.
 *
 * Eşleşme bulunamazsa mesaj kaybolmasın diye genel adrese (Ayarlar > mail,
 * support@ornek-holding.com) düşer.
 */
class BirimYonlendirme
{
    /**
     * Formdaki "ilgili birim" listesi: [türkçe grup adı => aktif dildeki etiket].
     *
     * Anahtar her zaman Türkçe grup adıdır — eşleşme tablosu (grup_mailler)
     * onunla kurulu ve dil değişince yönlendirme bozulmasın. Ziyaretçi
     * İngilizce sitede "Energy" görür, forma "Enerji" gider.
     *
     * Kaynak, sitede gerçekten yayında olan sektör aileleri; ziyaretçiye
     * karşılığı olmayan bir seçenek gösterilmez.
     *
     * @return array<string, string>
     */
    public function secenekler(): array
    {
        return Hizmet::yayinda()
            ->orderBy('sira')
            ->get()
            ->filter(fn ($h) => (string) $h->grup !== '')
            ->unique('grup')
            ->mapWithKeys(fn ($h) => [$h->grup => $h->d('grup')])
            ->all();
    }

    /** Doğrulamada kullanılacak geçerli değerler. */
    public function gecerliDegerler(): array
    {
        return array_keys($this->secenekler());
    }

    /** Sektör ailesi -> e-posta eşleşmesi. */
    public function harita(): array
    {
        $harita = [];

        foreach (Metin::etiketliListe(Ayar::al('grup_mailler')) as $satir) {
            if (filter_var($satir['deger'], FILTER_VALIDATE_EMAIL)) {
                $harita[$satir['etiket']] = $satir['deger'];
            }
        }

        return $harita;
    }

    /** Genel/yedek adres. */
    public function genelAdres(): string
    {
        $adres = trim(Ayar::al('mail'));

        return filter_var($adres, FILTER_VALIDATE_EMAIL) ? $adres : '';
    }

    /**
     * Verilen birim için hedef adresi döndürür.
     * Birim boşsa ya da eşleşme yoksa genel adrese düşer.
     */
    public function adres(?string $birim): string
    {
        $birim = trim((string) $birim);

        if ($birim !== '') {
            $harita = $this->harita();

            if (isset($harita[$birim])) {
                return $harita[$birim];
            }
        }

        return $this->genelAdres();
    }
}

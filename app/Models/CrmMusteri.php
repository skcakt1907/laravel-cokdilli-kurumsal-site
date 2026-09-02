<?php

namespace App\Models;

use App\Concerns\OzelAlanlar;
use Illuminate\Database\Eloquent\Model;

/** CRM müşterisi / adayı. */
class CrmMusteri extends Model
{
    use OzelAlanlar;

    protected $table = 'crm_customers';

    /**
     * `sorumlu_id` bilinçli olarak dışarıda: sorumlu ataması yalnızca
     * yetki kontrolünden geçtikten sonra açıkça yapılır, form gövdesinden
     * gelen veriyle değişemez.
     */
    protected $fillable = [
        'adi', 'unvan', 'email', 'telefon', 'sektor',
        'kaynak', 'ulke', 'adres', 'durum', 'etiketler', 'mesaj_id',
    ];

    /** Veritabanı varsayılanı bellekte de geçerli olsun (bkz. CrmFirsat). */
    protected $attributes = ['durum' => 'aday'];

    protected function casts(): array
    {
        return ['etiketler' => 'array'];
    }

    /** Özel alan sisteminde bu kaydın türü. */
    public function alanTablosu(): string
    {
        return 'musteri';
    }

    public function sorumlu()   { return $this->belongsTo(Kullanici::class, 'sorumlu_id'); }
    public function firsatlar() { return $this->hasMany(CrmFirsat::class, 'musteri_id')->latest(); }
    public function notlar()    { return $this->hasMany(CrmNot::class, 'musteri_id')->latest(); }
    public function mesaj()     { return $this->belongsTo(Mesaj::class, 'mesaj_id'); }

    /**
     * Bu kişiden gelen tüm site mesajları.
     *
     * Bağ e-posta üzerinden kurulur: aynı kişi defalarca yazdığında hepsi
     * tek müşteriye düştüğü için mesaj_id yalnızca ilk temasa işaret eder.
     */
    public function mesajlar()
    {
        if (!$this->email) {
            return Mesaj::whereKey($this->mesaj_id)->orderByDesc('tarih');
        }

        return Mesaj::where(fn ($q) => $q
                ->whereRaw('LOWER(mail) = ?', [mb_strtolower($this->email)])
                ->orWhere('id', $this->mesaj_id))
            ->orderByDesc('tarih');
    }

    /** Temsilci yalnızca sorumlusu olduğu kayıtları görür. */
    public function scopeGorebilecegi($q, Kullanici $kullanici)
    {
        return $kullanici->yoneticiMi() ? $q : $q->where('sorumlu_id', $kullanici->id);
    }

    public function scopeAra($q, ?string $terim)
    {
        $terim = trim((string) $terim);
        if ($terim === '') {
            return $q;
        }

        return $q->where(function ($alt) use ($terim) {
            foreach (['adi', 'unvan', 'email', 'telefon', 'sektor'] as $alan) {
                $alt->orWhere($alan, 'like', '%' . $terim . '%');
            }
        });
    }
}

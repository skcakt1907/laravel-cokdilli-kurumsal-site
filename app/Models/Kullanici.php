<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Panel kullanıcısı — düz PHP sürümünden devralınan `admin` tablosu.
 *
 * Laravel'in beklediği isimlerden iki sapma var, ikisi de tabloyu
 * değiştirmemek için bilinçli:
 *   - şifre kolonu `password` değil `sifre_hash`  -> getAuthPassword()
 *   - kullanıcı adı `email` değil `kullanici`
 *
 * Roller (yetki genişliğine göre artan):
 *   temsilci  - yalnızca sorumlusu olduğu CRM kayıtlarını görür
 *   yonetici  - tüm CRM kayıtları + site içeriği
 *   sahip     - her şey + kullanıcı yönetimi
 */
class Kullanici extends Authenticatable implements AuthenticatableContract
{
    use Notifiable;

    protected $table      = 'admin';
    public    $timestamps = false;

    /**
     * `rol` ve `durum` bilinçli olarak dışarıda: kullanıcı kendi profilini
     * düzenlerken kendini "sahip" yapamasın. Bu iki alan yalnızca
     * kullanıcı yönetimi ekranından, yetki kontrolünden geçtikten sonra
     * açıkça atanır.
     */
    protected $fillable = ['kullanici', 'ad_soyad', 'eposta', 'telefon'];

    protected $hidden = ['sifre_hash', 'remember_token'];

    protected function casts(): array
    {
        return [
            'durum'      => 'boolean',
            'son_giris'  => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /** Laravel `password` arar; bizim kolon `sifre_hash`. */
    public function getAuthPassword(): string
    {
        return $this->sifre_hash;
    }

    /**
     * Girişte otomatik yeniden hash'leme bu ada yazar. Belirtilmezse
     * Laravel olmayan `password` kolonunu güncellemeye çalışır ve patlar.
     *
     * Yeniden hash'leme bilinçli olarak açık: eski kayıtlar bcrypt cost 10,
     * Laravel'in varsayılanı 12. Her girişte kademeli olarak güçlenir ve
     * düz PHP sürümünün password_verify() çağrısı da çalışmaya devam eder.
     */
    public function getAuthPasswordName(): string
    {
        return 'sifre_hash';
    }

    // ---- Roller --------------------------------------------------------

    public const ROLLER = [
        'sahip'    => 'Sahip',
        'yonetici' => 'Yönetici',
        'temsilci' => 'Temsilci',
    ];

    public function sahipMi(): bool    { return $this->rol === 'sahip'; }
    public function yoneticiMi(): bool { return in_array($this->rol, ['sahip', 'yonetici'], true); }

    public function rolAdi(): string
    {
        return self::ROLLER[$this->rol] ?? $this->rol;
    }

    // ---- İlişkiler -----------------------------------------------------

    public function musteriler()
    {
        return $this->hasMany(CrmMusteri::class, 'sorumlu_id');
    }

    public function firsatlar()
    {
        return $this->hasMany(CrmFirsat::class, 'sorumlu_id');
    }

    public function gorevler()
    {
        return $this->hasMany(CrmGorev::class, 'sorumlu_id');
    }

    // ---- Sorgular ------------------------------------------------------

    public function scopeAktif($q)
    {
        return $q->where('durum', 1);
    }
}

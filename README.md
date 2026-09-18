# Cok Dilli Kurumsal Site (Laravel)

Holding yapisi icin sektor ailelerine gore yonlendirmeli kurumsal site.

## Ozellikler

- Sektor ailesi bazli form yonlendirme servisi
- Cok dilli icerik ve RTL destegi
- Yonetim panelinden bolum/birim ve e-posta yonlendirme ayarlari

## Kullanilan teknolojiler

Laravel 13 - PHP 8.3 - MySQL - Blade

## Bu depo hakkinda

Gercek bir musteri projesinin **portfolyo icin yayinlanmis** surumudur.
Yayina hazirlanirken canli alan adlari, gercek iletisim bilgileri, musteri
kayitlari ve uygulama anahtarlari ornek degerlerle degistirilmistir.
Kod ve mimari oldugu gibidir; veri gercek degildir.

## Kurulum

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

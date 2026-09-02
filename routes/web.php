<?php

use App\Http\Controllers\Panel\AlanController;
use App\Http\Controllers\Panel\AyarController;
use App\Http\Controllers\Panel\FirsatController;
use App\Http\Controllers\Panel\GirisController;
use App\Http\Controllers\Panel\HizmetController;
use App\Http\Controllers\Panel\KullaniciController;
use App\Http\Controllers\Panel\MesajController;
use App\Http\Controllers\Panel\MusteriController;
use App\Http\Controllers\Panel\PanoController;
use App\Http\Controllers\Panel\ProjeController;
use App\Http\Controllers\Panel\YaziController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Site (herkese açık)
|--------------------------------------------------------------------------
| Tanıtım sayfaları buraya gelecek. Slug'lar düz PHP sürümüyle birebir
| aynı tutulacak — canlıdaki adresler ve arama motoru kayıtları bozulmasın.
*/

Route::name('site.')->group(function () {
    Route::get('/',                          [SiteController::class, 'anasayfa'])->name('anasayfa');
    Route::get('kurumsal',                   [SiteController::class, 'kurumsal'])->name('kurumsal');
    Route::get('baskan-mesaji',              [SiteController::class, 'baskanMesaji'])->name('baskan');
    Route::get('faaliyet-alanlari',          [SiteController::class, 'faaliyetler'])->name('faaliyetler');
    Route::get('faaliyet-alanlari/{slug}',   [SiteController::class, 'faaliyet'])->name('faaliyet');
    Route::get('is-ortaklari',               [SiteController::class, 'ortaklar'])->name('ortaklar');
    Route::get('is-ortaklari/{slug}',        [SiteController::class, 'ortak'])->name('ortak');
    Route::get('yonetici/{id}',              [SiteController::class, 'yonetici'])->name('yonetici');
    Route::get('haberler',                   [SiteController::class, 'haberler'])->name('haberler');
    Route::get('haberler/{slug}',            [SiteController::class, 'haber'])->name('haber');
    Route::get('iletisim',                   [SiteController::class, 'iletisim'])->name('iletisim');
    Route::post('iletisim',                  [SiteController::class, 'iletisimGonder'])->name('iletisim.gonder');
});

/*
| Eski adres uyumu — düz PHP sürümünde detay sayfaları ?slug= ile
| çalışıyordu. Canlıdaki bağlantılar ve arama motoru kayıtları
| kırılmasın diye kalıcı yönlendirme veriliyor.
*/
Route::get('faaliyet-detay', fn () => redirect()->route('site.faaliyet', request('slug'), 301));
Route::get('ortak-detay',    fn () => redirect()->route('site.ortak', request('slug'), 301));
Route::get('haber-detay',    fn () => redirect()->route('site.haber', request('slug'), 301));
Route::get('yonetici-detay', fn () => redirect()->route('site.yonetici', request('id'), 301));

/*
|--------------------------------------------------------------------------
| Panel
|--------------------------------------------------------------------------
*/

Route::prefix('yonetim')->name('panel.')->group(function () {

    // Oturum açmamış kullanıcılar
    Route::middleware('guest')->group(function () {
        Route::get('giris',  [GirisController::class, 'form'])->name('giris');
        Route::post('giris', [GirisController::class, 'gir'])->name('giris.gonder');
    });

    // Oturum açmış kullanıcılar — 'rol' parametresiz çağrıldığında
    // yalnızca hesabın aktif olduğunu doğrular.
    Route::middleware(['auth', 'rol'])->group(function () {
        Route::post('cikis', [GirisController::class, 'cik'])->name('cikis');
        Route::get('/', [PanoController::class, 'goster'])->name('pano');

        // --- CRM: Müşteriler ---
        Route::prefix('musteriler')->name('musteri.')->group(function () {
            Route::get('/',                  [MusteriController::class, 'index'])->name('index');
            Route::get('yeni',               [MusteriController::class, 'olusturForm'])->name('olustur');
            Route::post('/',                 [MusteriController::class, 'kaydet'])->name('kaydet');
            Route::get('{musteri}',          [MusteriController::class, 'detay'])->name('detay');
            Route::get('{musteri}/duzenle',  [MusteriController::class, 'duzenleForm'])->name('duzenle');
            Route::put('{musteri}',          [MusteriController::class, 'guncelle'])->name('guncelle');
            Route::delete('{musteri}',       [MusteriController::class, 'sil'])->name('sil');
            Route::post('{musteri}/not',     [MusteriController::class, 'notEkle'])->name('not');
        });

        // --- CRM: Fırsatlar ---
        Route::prefix('firsatlar')->name('firsat.')->group(function () {
            Route::get('/',                 [FirsatController::class, 'pano'])->name('pano');
            Route::get('yeni',              [FirsatController::class, 'olusturForm'])->name('olustur');
            Route::post('/',                [FirsatController::class, 'kaydet'])->name('kaydet');
            Route::get('{firsat}/duzenle',  [FirsatController::class, 'duzenleForm'])->name('duzenle');
            Route::put('{firsat}',          [FirsatController::class, 'guncelle'])->name('guncelle');
            Route::post('{firsat}/tasi',    [FirsatController::class, 'tasi'])->name('tasi');
            Route::delete('{firsat}',       [FirsatController::class, 'sil'])->name('sil');
        });

        // --- Site yönetimi: yönetici ve üstü ---
        Route::middleware('rol:sahip,yonetici')->group(function () {

            Route::prefix('mesajlar')->name('mesaj.')->group(function () {
                Route::get('/',                 [MesajController::class, 'index'])->name('index');
                Route::get('{mesaj}',           [MesajController::class, 'detay'])->name('detay');
                Route::post('{mesaj}/okunmadi', [MesajController::class, 'okunmadi'])->name('okunmadi');
                Route::post('{mesaj}/crm',      [MesajController::class, 'crmeAktar'])->name('crm');
                Route::delete('{mesaj}',        [MesajController::class, 'sil'])->name('sil');
            });

            // Ayarlar bölüm bölüm açılır; /ayarlar ilk bölüme yönlendirir.
            // mail-testi rotası {bolum}'den ÖNCE tanımlı olmalı, yoksa
            // "mail-testi" bir bölüm adı sanılır.
            Route::get('ayarlar',             [AyarController::class, 'index'])->name('ayar.index');
            Route::post('ayarlar',            [AyarController::class, 'kaydet'])->name('ayar.kaydet');
            Route::post('ayarlar/mail-testi', [AyarController::class, 'mailTesti'])->name('ayar.mailTesti');
            Route::get('ayarlar/{bolum}',     [AyarController::class, 'bolum'])->name('ayar.bolum');

            // İçerik CRUD'ları — üçü de aynı kalıpta
            Route::prefix('faaliyet-alanlari')->name('hizmet.')->group(function () {
                Route::get('/',                [HizmetController::class, 'index'])->name('index');
                Route::get('yeni',             [HizmetController::class, 'olusturForm'])->name('olustur');
                Route::post('/',               [HizmetController::class, 'kaydet'])->name('kaydet');
                Route::get('{hizmet}/duzenle', [HizmetController::class, 'duzenleForm'])->name('duzenle');
                Route::put('{hizmet}',         [HizmetController::class, 'guncelle'])->name('guncelle');
                Route::post('{hizmet}/durum',  [HizmetController::class, 'durumDegistir'])->name('durum');
                Route::delete('{hizmet}',      [HizmetController::class, 'sil'])->name('sil');
            });

            Route::prefix('is-ortaklari')->name('proje.')->group(function () {
                Route::get('/',               [ProjeController::class, 'index'])->name('index');
                Route::get('yeni',            [ProjeController::class, 'olusturForm'])->name('olustur');
                Route::post('/',              [ProjeController::class, 'kaydet'])->name('kaydet');
                Route::get('{proje}/duzenle', [ProjeController::class, 'duzenleForm'])->name('duzenle');
                Route::put('{proje}',         [ProjeController::class, 'guncelle'])->name('guncelle');
                Route::post('{proje}/durum',  [ProjeController::class, 'durumDegistir'])->name('durum');
                Route::delete('{proje}',      [ProjeController::class, 'sil'])->name('sil');
            });

            Route::prefix('haberler')->name('yazi.')->group(function () {
                Route::get('/',              [YaziController::class, 'index'])->name('index');
                Route::get('yeni',           [YaziController::class, 'olusturForm'])->name('olustur');
                Route::post('/',             [YaziController::class, 'kaydet'])->name('kaydet');
                Route::get('{yazi}/duzenle', [YaziController::class, 'duzenleForm'])->name('duzenle');
                Route::put('{yazi}',         [YaziController::class, 'guncelle'])->name('guncelle');
                Route::post('{yazi}/durum',  [YaziController::class, 'durumDegistir'])->name('durum');
                Route::delete('{yazi}',      [YaziController::class, 'sil'])->name('sil');
            });
        });

        // --- Sistem: yalnızca sahip ---
        Route::middleware('rol:sahip')->prefix('kullanicilar')->name('kullanici.')->group(function () {
            Route::get('/',                   [KullaniciController::class, 'index'])->name('index');
            Route::get('yeni',                [KullaniciController::class, 'olusturForm'])->name('olustur');
            Route::post('/',                  [KullaniciController::class, 'kaydet'])->name('kaydet');
            Route::get('{kullanici}/duzenle', [KullaniciController::class, 'duzenleForm'])->name('duzenle');
            Route::put('{kullanici}',         [KullaniciController::class, 'guncelle'])->name('guncelle');
            Route::post('{kullanici}/durum',  [KullaniciController::class, 'durumDegistir'])->name('durum');
            Route::delete('{kullanici}',      [KullaniciController::class, 'sil'])->name('sil');
        });

        Route::middleware('rol:sahip')->prefix('ozel-alanlar')->name('alan.')->group(function () {
            Route::get('/',              [AlanController::class, 'index'])->name('index');
            Route::get('yeni',           [AlanController::class, 'olusturForm'])->name('olustur');
            Route::post('/',             [AlanController::class, 'kaydet'])->name('kaydet');
            Route::get('{alan}/duzenle', [AlanController::class, 'duzenleForm'])->name('duzenle');
            Route::put('{alan}',         [AlanController::class, 'guncelle'])->name('guncelle');
            Route::post('{alan}/durum',  [AlanController::class, 'durumDegistir'])->name('durum');
            Route::delete('{alan}',      [AlanController::class, 'sil'])->name('sil');
        });
    });
});

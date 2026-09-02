@extends('panel.layout')
@section('baslik', 'Ayarlar — ' . $baslik)

@section('icerik')

<div class="ayar-duzen">

  @include('panel.ayar._nav', ['bolumler' => $bolumler, 'aktif' => $aktif])

  <div class="ayar-icerik">

    {{-- ===================== MAİL VE BİLDİRİM ===================== --}}
    @if($aktif === 'mail')
      {{-- Veriler AyarController::mailVerisi()'nden gelir. --}}
      <div class="card p-4 mb-4">
        <h5 style="color:#fff;font-size:1rem" class="mb-1">Gönderim ayarları</h5>
        <p style="font-size:.85rem;color:var(--metin-sonuk)" class="mb-3">
          Bu ayarlar sunucudaki <code>.env</code> dosyasından okunur, panelden değiştirilmez.
        </p>

        @if($kipLog)
          <div class="alert alert-warning" style="font-size:.87rem">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Gönderim kapalı.</strong> <code>MAIL_MAILER=log</code> ayarlı —
            mesajlar gerçekten gönderilmiyor, log dosyasına yazılıyor.
            Canlıda <code>MAIL_MAILER=smtp</code> olmalı.
          </div>
        @elseif(!$sifreVar)
          <div class="alert alert-danger" style="font-size:.87rem">
            <i class="bi bi-x-octagon me-2"></i>
            <strong>Şifre girilmemiş.</strong> <code>.env</code> içindeki
            <code>MAIL_PASSWORD</code> boş; bildirimler gönderilemez.
          </div>
        @endif

        <dl class="row mb-0" style="font-size:.87rem">
          @foreach($smtp as $etiket => $deger)
            <dt class="col-sm-4">{{ $etiket }}</dt>
            <dd class="col-sm-8">{{ $deger ?: '—' }}</dd>
          @endforeach

          <dt class="col-sm-4">Şifre</dt>
          <dd class="col-sm-8">
            @if($sifreVar)
              <i class="bi bi-check-circle me-1" style="color:#4caf50"></i>girilmiş
            @else
              <i class="bi bi-x-circle me-1" style="color:#e57373"></i>boş
            @endif
          </dd>
        </dl>
      </div>

      {{-- Düz gönderim testi: SMTP ayarı doğru mu? --}}
      <div class="card p-4 mb-4">
        <h5 style="color:#fff;font-size:1rem" class="mb-1">Test e-postası gönder</h5>
        <p style="font-size:.85rem;color:var(--metin-sonuk)" class="mb-3">
          Kendi adresine bir deneme gönder. Ulaşırsa gönderim ayarları çalışıyor demektir.
        </p>

        <form method="post" action="{{ route('panel.ayar.mailTesti') }}" class="row g-2 align-items-end">
          @csrf
          <input type="hidden" name="hedef_tipi" value="adres">
          <div class="col-md-8">
            <label class="form-label" for="test_adres">E-posta adresi</label>
            <input type="email" class="form-control" id="test_adres" name="test_adres"
                   value="{{ old('test_adres', auth()->user()->eposta) }}"
                   placeholder="ornek@ornek-holding.com" required>
          </div>
          <div class="col-md-4">
            <button class="btn btn-primary w-100"><i class="bi bi-send me-1"></i> Gönder</button>
          </div>
        </form>
      </div>

      {{-- Yönlendirme testi: mesaj doğru departmana mı gidiyor? --}}
      <div class="card p-4">
        <h5 style="color:#fff;font-size:1rem" class="mb-1">Faaliyet alanı yönlendirmesi</h5>
        <p style="font-size:.85rem;color:var(--metin-sonuk)" class="mb-3">
          İletişim formundan gelen mesaj, ziyaretçinin seçtiği faaliyet alanına göre
          aşağıdaki adrese gider. Eşleşmeyi <strong>İletişim</strong> bölümündeki
          “Faaliyet alanı yönlendirmesi” alanından değiştirebilirsin.
        </p>

        <div class="table-responsive mb-3">
          <table class="table table-sm mb-0" style="font-size:.87rem">
            <thead><tr><th>Faaliyet alanı</th><th>Gideceği adres</th></tr></thead>
            <tbody>
              @foreach($satirlar as $satir)
                <tr>
                  <td>{{ $satir['ad'] }}</td>
                  <td>
                    {{ $satir['hedef'] ?: '— tanımsız —' }}
                    @unless($satir['eslesme'])
                      <span class="badge bg-warning text-dark ms-1">eşleşme yok — genele düşüyor</span>
                    @endunless
                  </td>
                </tr>
              @endforeach
              <tr>
                <td><em>Seçilmedi / Genel</em></td>
                <td>{{ $genelAdres ?: '— tanımsız —' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <form method="post" action="{{ route('panel.ayar.mailTesti') }}" class="row g-2 align-items-end">
          @csrf
          <input type="hidden" name="hedef_tipi" value="birim">
          <div class="col-md-8">
            <label class="form-label" for="test_birim">Bu alana test gönder</label>
            <select class="form-select" id="test_birim" name="test_birim" required>
              @foreach($satirlar as $satir)
                <option value="{{ $satir['ad'] }}">{{ $satir['ad'] }} → {{ $satir['hedef'] }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <button class="btn btn-outline-secondary w-100">
              <i class="bi bi-signpost-split me-1"></i> Yönlendirmeyi dene
            </button>
          </div>
        </form>
      </div>

    {{-- ===================== LOGO ===================== --}}
    @elseif($aktif === 'logo')
      <form method="post" action="{{ route('panel.ayar.kaydet') }}" enctype="multipart/form-data" novalidate>
        @csrf
        <div class="card p-4">
          <div class="row g-4 align-items-start">
            <div class="col-md-4">
              <label class="form-label">Mevcut logo</label>
              <div class="p-3 text-center" style="background:var(--yuzey-2);border-radius:10px">
                <img src="{{ \App\Support\Metin::logoUrl() }}" alt="Logo"
                     style="max-width:100%;max-height:120px">
              </div>
              @if(($degerler['logo'] ?? '') !== '')
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" name="logo_sil" value="1" id="logo_sil">
                  <label class="form-check-label" for="logo_sil">Yüklenen logoyu kaldır</label>
                </div>
                <div class="form-text">Kaldırılırsa varsayılan logo kullanılır.</div>
              @endif
            </div>

            <div class="col-md-8">
              <label class="form-label" for="logo">Yeni logo yükle</label>
              <input type="file" class="form-control" id="logo" name="logo" accept="image/png,image/jpeg,image/webp">
              <div class="form-text">
                PNG, JPG veya WebP · en fazla 2 MB. Koyu zeminde duracağı için
                <strong>şeffaf arka planlı ve açık renkli</strong> bir dosya en iyi sonucu verir.
              </div>
            </div>
          </div>
        </div>

        {{-- ---- Sekme simgesi ---- Veriler AyarController::simgeVerisi()'nden. --}}
        <div class="card p-4 mt-4">
          <h5 style="color:#fff;font-size:1rem" class="mb-1">Sekme simgesi</h5>
          <p style="font-size:.85rem;color:var(--metin-sonuk)" class="mb-3">
            Tarayıcı sekmesinde ve yer imlerinde görünen küçük simge.
            Panel giriş ekranında da bu kullanılır.
          </p>

          <div class="row g-4 align-items-start">
            <div class="col-md-4">
              <label class="form-label">Şu an kullanılan</label>
              <div class="p-3 text-center" style="background:var(--yuzey-2);border-radius:10px">
                <img src="{{ $simgeOnizleme }}" alt="Sekme simgesi"
                     style="width:64px;height:64px;object-fit:contain">
              </div>
              <div class="form-text">
                {{ $simgeVar ? 'Panelden yüklendi' : 'Varsayılan (pakette gelen)' }}
              </div>

              @if($simgeVar)
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" name="favicon_sil" value="1" id="favicon_sil">
                  <label class="form-check-label" for="favicon_sil">Yüklenen simgeyi kaldır</label>
                </div>
              @endif
            </div>

            <div class="col-md-8">
              <label class="form-label" for="favicon">Yeni simge yükle</label>
              <input type="file" class="form-control" id="favicon" name="favicon"
                     accept="image/png,image/jpeg,image/webp">
              <div class="form-text">
                PNG, JPG veya WebP · en fazla 1 MB.
                Kare olması şart değil — kenar boşluğu kırpılıp otomatik kareye
                oturtulur ve 16, 32, 180 piksel sürümleri üretilir.
                <br>
                <strong>16 pikselde ince detay kaybolur</strong>; sade bir amblem
                ya da harf en iyi sonucu verir.
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Kaydet</button>
        </div>
      </form>

    {{-- ===================== ALAN BÖLÜMLERİ ===================== --}}
    @else
      {{-- enctype: Kurumsal bölümünde CEO video dosyası yüklenebiliyor --}}
      <form method="post" action="{{ route('panel.ayar.kaydet') }}"
            enctype="multipart/form-data" novalidate>
        @csrf
        <div class="card p-4">
          <div class="row g-3">
            @foreach($alanlar as $anahtar => $tanim)
              @php([$etiket, $tip] = $tanim)
              @php($ipucu = $tanim[2] ?? null)
              @php($ciftDil = in_array($anahtar, $ciftDilli, true))
              @php($genis = $tip === 'uzun')

              <div class="{{ $genis ? 'col-12' : 'col-md-6' }}">
                <label class="form-label" for="a_{{ $anahtar }}">
                  {{ $etiket }}
                  @if($ciftDil)<span class="en-hint">TR</span>@endif
                </label>

                @if($tip === 'uzun')
                  <textarea class="form-control" id="a_{{ $anahtar }}" name="ayar[{{ $anahtar }}]"
                            rows="{{ $anahtar === 'baskan_mesaji' ? 14 : 3 }}">{{ $degerler[$anahtar] ?? '' }}</textarea>
                @elseif($tip === 'sayi')
                  <input type="number" min="0" class="form-control" id="a_{{ $anahtar }}"
                         name="ayar[{{ $anahtar }}]" value="{{ $degerler[$anahtar] ?? '' }}">
                @elseif($tip === 'url')
                  <input type="url" class="form-control" id="a_{{ $anahtar }}"
                         name="ayar[{{ $anahtar }}]" value="{{ $degerler[$anahtar] ?? '' }}"
                         placeholder="https://">
                @else
                  <input type="text" class="form-control" id="a_{{ $anahtar }}"
                         name="ayar[{{ $anahtar }}]" value="{{ $degerler[$anahtar] ?? '' }}">
                @endif

                @if($ipucu)<div class="form-text">{{ $ipucu }}</div>@endif

                {{-- Çeviriler: aktif dil sayısı kadar alan üretilir --}}
                @if($ciftDil)
                  @foreach(\App\Support\Dil::cevrilecek() as $dKod => $dTanim)
                    <label class="form-label mt-2" for="a_{{ $anahtar }}_{{ $dKod }}">
                      {{ $etiket }} <span class="en-hint">{{ strtoupper($dKod) }}</span>
                    </label>
                    @php($dDeger = \App\Models\Ayar::dildeki($anahtar, $dKod))
                    @if($tip === 'uzun')
                      <textarea class="form-control" id="a_{{ $anahtar }}_{{ $dKod }}"
                                name="ceviri[{{ $dKod }}][{{ $anahtar }}]"
                                @if(($dTanim['yon'] ?? 'ltr') === 'rtl') dir="rtl" @endif
                                rows="{{ $anahtar === 'baskan_mesaji' ? 14 : 3 }}">{{ $dDeger }}</textarea>
                    @else
                      <input type="text" class="form-control" id="a_{{ $anahtar }}_{{ $dKod }}"
                             name="ceviri[{{ $dKod }}][{{ $anahtar }}]" value="{{ $dDeger }}"
                             @if(($dTanim['yon'] ?? 'ltr') === 'rtl') dir="rtl" @endif>
                    @endif
                  @endforeach
                  <div class="form-text">Boş bırakılan çeviri için sitede Türkçesi görünür.</div>
                @endif
              </div>
            @endforeach
          </div>
        </div>

        {{-- CEO Digital Message: başkan mesajı sayfasında metnin üstünde çıkar --}}
        @if($aktif === 'kurumsal')
          <div class="card p-4 mt-4">
            <h5 style="color:#fff;font-size:1rem" class="mb-1">Başkan mesajı videosu</h5>
            <p style="font-size:.85rem;color:var(--metin-sonuk)" class="mb-3">
              Girilirse Başkan'ın Mesajı sayfasında metnin üstünde oynatıcı görünür.
              Boş bırakılırsa sayfa bugünkü gibi yalnızca metin gösterir.
            </p>
            <div class="row g-3">
              @include('panel.ortak.video-alan', [
                'deger' => $degerler['baskan_video'] ?? null, 'etiket' => 'Video',
              ])
            </div>
          </div>
        @endif

        <div class="mt-4">
          <button class="btn btn-primary"><i class="bi bi-save me-1"></i> {{ $baslik }} Ayarlarını Kaydet</button>
        </div>
      </form>
    @endif

  </div>
</div>

@endsection

@extends('panel.layout')
@section('baslik', $proje->exists ? 'Kaydı Düzenle' : 'Yeni Şirket / İş Ortağı')

@section('icerik')

<form method="post" enctype="multipart/form-data" novalidate
      action="{{ $proje->exists ? route('panel.proje.guncelle', $proje) : route('panel.proje.kaydet') }}">
  @csrf
  @if($proje->exists) @method('put') @endif

  <div class="card p-4">
    <div class="row g-3">

      @include('panel.ortak.dilli-alan', [
        'ad' => 'baslik', 'etiket' => 'Ad', 'kayit' => $proje, 'zorunlu' => true,
      ])

      <div class="col-md-6">
        <label class="form-label" for="f_tur">Tür <span style="color:var(--ap)">*</span></label>
        <select class="form-select" id="f_tur" name="tur" required>
          @foreach($turler as $anahtar => $ad)
            <option value="{{ $anahtar }}" @selected(old('tur', $proje->tur ?: 'ortak') === $anahtar)>{{ $ad }}</option>
          @endforeach
        </select>
        <div class="form-text">
          Grup şirketleri ve iş ortakları sitede ayrı bölümlerde listelenir.
        </div>
      </div>

      <div class="col-md-6">
        <label class="form-label" for="f_kategori">Sektör</label>
        <input type="text" class="form-control" id="f_kategori" name="kategori" list="alanlar"
               value="{{ old('kategori', $proje->kategori) }}" maxlength="80">
        <datalist id="alanlar">
          @foreach($alanlar as $alan)<option value="{{ $alan }}">@endforeach
        </datalist>
        <div class="form-text">
          Bir faaliyet alanının başlığıyla birebir aynı yazarsanız, o sektörün
          sayfasında da iş ortağı olarak görünür.
        </div>

        {{-- Sektör çevirileri: aktif dil sayısı kadar alan üretilir --}}
        @foreach(\App\Support\Dil::cevrilecek() as $dKod => $dTanim)
          <label class="form-label mt-2" for="f_kategori_{{ $dKod }}">
            Sektör <span class="en-hint">{{ strtoupper($dKod) }}</span>
          </label>
          <input type="text" class="form-control" id="f_kategori_{{ $dKod }}"
                 name="ceviri[{{ $dKod }}][kategori]" maxlength="80"
                 @if(($dTanim['yon'] ?? 'ltr') === 'rtl') dir="rtl" @endif
                 value="{{ old("ceviri.{$dKod}.kategori", $proje->exists ? $proje->ceviri('kategori', $dKod) : '') }}">
        @endforeach
      </div>

      @include('panel.ortak.dilli-alan', [
        'ad' => 'ulke', 'etiket' => 'Ülke', 'kayit' => $proje,
      ])

      @include('panel.ortak.dilli-alan', [
        'ad' => 'aciklama', 'etiket' => 'Açıklama', 'kayit' => $proje, 'tip' => 'uzun', 'satir' => 8,
        'ipucu' => 'Boş satır yeni paragraf açar. "- " ile madde, "## " ile ara başlık yazılır.',
      ])

      <div class="col-md-4">
        <label class="form-label" for="f_website">Web Sitesi</label>
        <input type="url" class="form-control" id="f_website" name="website"
               value="{{ old('website', $proje->website) }}" maxlength="255" placeholder="https://">
      </div>

      <div class="col-md-4">
        <label class="form-label" for="f_tarih">Kuruluş Yılı</label>
        <input type="text" class="form-control" id="f_tarih" name="tarih"
               value="{{ old('tarih', $proje->tarih) }}" maxlength="40" placeholder="2018">
      </div>

      <div class="col-md-4">
        <label class="form-label" for="f_sira">Sıra</label>
        <input type="number" class="form-control" id="f_sira" name="sira" min="0" max="999"
               value="{{ old('sira', $proje->sira ?? 0) }}">
      </div>

      @include('panel.ortak.gorsel-alan', [
        'kayit' => $proje, 'etiket' => 'Logo',
        'ipucu' => 'PNG, JPG, WebP veya GIF · en fazla 4 MB. Logolar beyaz zeminde gösterilir.',
      ])

      <div class="col-12">
        <div class="form-check">
          <input type="hidden" name="durum" value="0">
          <input class="form-check-input" type="checkbox" name="durum" value="1" id="f_durum"
                 @checked(old('durum', $proje->exists ? $proje->durum : true))>
          <label class="form-check-label" for="f_durum">Sitede yayında</label>
        </div>

        @if($proje->exists)
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="slug_yenile" value="1" id="f_slug">
            <label class="form-check-label" for="f_slug" style="font-size:.86rem">
              Adresi ada göre yenile
            </label>
          </div>
          <div class="form-text">
            Şu anki adres: <code>/is-ortaklari/{{ $proje->slug }}</code>
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Kaydet</button>
    <a href="{{ route('panel.proje.index') }}" class="btn btn-outline-secondary">İptal</a>

    @if($proje->exists)
      <button type="submit" form="silFormu" class="btn btn-outline-danger ms-auto"
              onclick="return confirm('{{ $proje->baslik }} silinecek. Emin misiniz?')">
        <i class="bi bi-trash me-1"></i> Sil
      </button>
    @endif
  </div>
</form>

{{-- Silme formu ana formun DIŞINDA: iç içe <form> geçersizdir ve
     içindeki _method=delete dış forma karışıp "Kaydet"i silmeye çevirir. --}}
@if($proje->exists)
  <form id="silFormu" method="post" action="{{ route('panel.proje.sil', $proje) }}" class="d-none">
    @csrf @method('delete')
  </form>
@endif

@endsection

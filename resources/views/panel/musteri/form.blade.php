@extends('panel.layout')
@section('baslik', $musteri->exists ? 'Müşteriyi Düzenle' : 'Yeni Müşteri')

@section('icerik')

<form method="post"
      action="{{ $musteri->exists ? route('panel.musteri.guncelle', $musteri) : route('panel.musteri.kaydet') }}"
      class="card p-4" novalidate>
  @csrf
  @if($musteri->exists) @method('put') @endif

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label" for="adi">Firma / Kişi Adı <span style="color:var(--ap)">*</span></label>
      <input type="text" class="form-control" id="adi" name="adi"
             value="{{ old('adi', $musteri->adi) }}" required maxlength="150" autofocus>
    </div>

    <div class="col-md-6">
      <label class="form-label" for="unvan">Ünvan / Yetkili</label>
      <input type="text" class="form-control" id="unvan" name="unvan"
             value="{{ old('unvan', $musteri->unvan) }}" maxlength="150">
    </div>

    <div class="col-md-6">
      <label class="form-label" for="email">E-posta</label>
      <input type="email" class="form-control" id="email" name="email"
             value="{{ old('email', $musteri->email) }}" maxlength="150">
    </div>

    <div class="col-md-6">
      <label class="form-label" for="telefon">Telefon</label>
      <input type="text" class="form-control" id="telefon" name="telefon"
             value="{{ old('telefon', $musteri->telefon) }}" maxlength="50">
    </div>

    <div class="col-md-4">
      <label class="form-label" for="sektor">Sektör</label>
      <input type="text" class="form-control" id="sektor" name="sektor"
             value="{{ old('sektor', $musteri->sektor) }}" maxlength="100">
    </div>

    <div class="col-md-4">
      <label class="form-label" for="kaynak">Kaynak</label>
      <input type="text" class="form-control" id="kaynak" name="kaynak" list="kaynaklar"
             value="{{ old('kaynak', $musteri->kaynak) }}" maxlength="100"
             placeholder="web formu, referans, fuar…">
      <datalist id="kaynaklar">
        <option value="web formu"><option value="referans"><option value="fuar">
        <option value="doğrudan iletişim"><option value="LinkedIn">
      </datalist>
    </div>

    <div class="col-md-4">
      <label class="form-label" for="ulke">Ülke</label>
      <input type="text" class="form-control" id="ulke" name="ulke"
             value="{{ old('ulke', $musteri->ulke) }}" maxlength="80">
    </div>

    <div class="col-md-6">
      <label class="form-label" for="durum">Durum <span style="color:var(--ap)">*</span></label>
      <select class="form-select" id="durum" name="durum" required>
        @foreach($durumlar as $anahtar => $etiket)
          <option value="{{ $anahtar }}" @selected(old('durum', $musteri->durum) === $anahtar)>{{ $etiket }}</option>
        @endforeach
      </select>
    </div>

    @if($kullanicilar->isNotEmpty())
      <div class="col-md-6">
        <label class="form-label" for="sorumlu_id">Sorumlu</label>
        <select class="form-select" id="sorumlu_id" name="sorumlu_id">
          <option value="">Atanmamış</option>
          @foreach($kullanicilar as $k)
            <option value="{{ $k->id }}"
              @selected((string) old('sorumlu_id', $musteri->sorumlu_id) === (string) $k->id)>
              {{ $k->ad_soyad ?: $k->kullanici }} ({{ $k->rolAdi() }})
            </option>
          @endforeach
        </select>
      </div>
    @endif

    <div class="col-12">
      <label class="form-label" for="adres">Adres</label>
      <textarea class="form-control" id="adres" name="adres" rows="2"
                maxlength="2000">{{ old('adres', $musteri->adres) }}</textarea>
    </div>
  </div>

  {{-- Panelden tanımlanmış özel alanlar --}}
  @if($ozelAlanlar->isNotEmpty())
    <hr class="my-4" style="border-color:var(--cizgi)">
    <h6 style="color:var(--ap);font-size:.75rem;letter-spacing:1.4px;text-transform:uppercase">Ek Bilgiler</h6>

    <div class="row g-3 mt-1">
      @foreach($ozelAlanlar as $alan)
        @php($ad = 'ozel[' . $alan->anahtar . ']')
        @php($mevcut = old('ozel.' . $alan->anahtar, $ozelDegerler[$alan->anahtar] ?? ''))

        <div class="{{ $alan->tip === 'uzun_metin' ? 'col-12' : 'col-md-4' }}">
          <label class="form-label" for="ozel_{{ $alan->anahtar }}">
            {{ $alan->etiketi() }}
            @if($alan->zorunlu)<span style="color:var(--ap)">*</span>@endif
          </label>

          @switch($alan->tip)
            @case('uzun_metin')
              <textarea class="form-control" id="ozel_{{ $alan->anahtar }}" name="{{ $ad }}"
                        rows="3" @required($alan->zorunlu)>{{ $mevcut }}</textarea>
              @break

            @case('sayi')
              <input type="number" step="any" class="form-control" id="ozel_{{ $alan->anahtar }}"
                     name="{{ $ad }}" value="{{ $mevcut }}" @required($alan->zorunlu)>
              @break

            @case('tarih')
              <input type="date" class="form-control" id="ozel_{{ $alan->anahtar }}"
                     name="{{ $ad }}" value="{{ $mevcut }}" @required($alan->zorunlu)>
              @break

            @case('secim')
              <select class="form-select" id="ozel_{{ $alan->anahtar }}" name="{{ $ad }}" @required($alan->zorunlu)>
                <option value="">Seçiniz</option>
                @foreach($alan->secenekListesi() as $secenek)
                  <option value="{{ $secenek }}" @selected($mevcut === $secenek)>{{ $secenek }}</option>
                @endforeach
              </select>
              @break

            @case('onay')
              <div class="form-check mt-2">
                <input type="hidden" name="{{ $ad }}" value="">
                <input class="form-check-input" type="checkbox" id="ozel_{{ $alan->anahtar }}"
                       name="{{ $ad }}" value="1" @checked($mevcut === '1')>
                <label class="form-check-label" for="ozel_{{ $alan->anahtar }}">Evet</label>
              </div>
              @break

            @default
              <input type="text" class="form-control" id="ozel_{{ $alan->anahtar }}"
                     name="{{ $ad }}" value="{{ $mevcut }}" @required($alan->zorunlu)>
          @endswitch
        </div>
      @endforeach
    </div>
  @endif

  <div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Kaydet</button>
    <a href="{{ $musteri->exists ? route('panel.musteri.detay', $musteri) : route('panel.musteri.index') }}"
       class="btn btn-outline-secondary">İptal</a>
  </div>
</form>

@endsection

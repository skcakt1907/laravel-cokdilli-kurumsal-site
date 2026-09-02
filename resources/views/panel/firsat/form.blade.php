@extends('panel.layout')
@section('baslik', $firsat->exists ? 'Fırsatı Düzenle' : 'Yeni Fırsat')

@section('icerik')

<form method="post"
      action="{{ $firsat->exists ? route('panel.firsat.guncelle', $firsat) : route('panel.firsat.kaydet') }}"
      class="card p-4" novalidate>
  @csrf
  @if($firsat->exists) @method('put') @endif

  <div class="row g-3">
    <div class="col-md-8">
      <label class="form-label" for="baslik">Başlık <span style="color:var(--ap)">*</span></label>
      <input type="text" class="form-control" id="baslik" name="baslik"
             value="{{ old('baslik', $firsat->baslik) }}" required maxlength="180" autofocus
             placeholder="ör. Umman rafineri ekipman tedariki">
    </div>

    <div class="col-md-4">
      <label class="form-label" for="musteri_id">Müşteri <span style="color:var(--ap)">*</span></label>
      <select class="form-select" id="musteri_id" name="musteri_id" required>
        <option value="">Seçiniz</option>
        @foreach($musteriler as $m)
          <option value="{{ $m->id }}"
            @selected((string) old('musteri_id', $firsat->musteri_id) === (string) $m->id)>{{ $m->adi }}</option>
        @endforeach
      </select>
      @if($musteriler->isEmpty())
        <div class="form-text">
          Önce <a href="{{ route('panel.musteri.olustur') }}">bir müşteri</a> eklemelisiniz.
        </div>
      @endif
    </div>

    <div class="col-md-5">
      <label class="form-label" for="stage_id">Aşama <span style="color:var(--ap)">*</span></label>
      <select class="form-select" id="stage_id" name="stage_id" required>
        @foreach($hatlar as $hat)
          <optgroup label="{{ $hat->adi }}">
            @foreach($hat->asamalar as $asama)
              <option value="{{ $asama->id }}"
                @selected((string) old('stage_id', $firsat->stage_id) === (string) $asama->id)>
                {{ $asama->adi }}@if($asama->sonuc === 'acik') (%{{ $asama->olasilik }})@endif
              </option>
            @endforeach
          </optgroup>
        @endforeach
      </select>
      <div class="form-text">Kapanış aşamaları fırsatın durumunu da değiştirir.</div>
    </div>

    <div class="col-md-4">
      <label class="form-label" for="tutar">Tutar</label>
      <input type="number" step="0.01" min="0" class="form-control" id="tutar" name="tutar"
             value="{{ old('tutar', $firsat->exists ? (float) $firsat->tutar : '') }}">
    </div>

    <div class="col-md-3">
      <label class="form-label" for="para_birimi">Para Birimi <span style="color:var(--ap)">*</span></label>
      <select class="form-select" id="para_birimi" name="para_birimi" required>
        @foreach(['USD', 'EUR', 'TRY', 'AED', 'OMR', 'GBP'] as $birim)
          <option value="{{ $birim }}"
            @selected(old('para_birimi', $firsat->para_birimi ?: 'USD') === $birim)>{{ $birim }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label" for="kapanis_tarihi">Tahmini Kapanış</label>
      <input type="date" class="form-control" id="kapanis_tarihi" name="kapanis_tarihi"
             value="{{ old('kapanis_tarihi', $firsat->kapanis_tarihi?->format('Y-m-d')) }}">
    </div>

    @if($kullanicilar->isNotEmpty())
      <div class="col-md-4">
        <label class="form-label" for="sorumlu_id">Sorumlu</label>
        <select class="form-select" id="sorumlu_id" name="sorumlu_id">
          <option value="">Atanmamış</option>
          @foreach($kullanicilar as $k)
            <option value="{{ $k->id }}"
              @selected((string) old('sorumlu_id', $firsat->sorumlu_id) === (string) $k->id)>
              {{ $k->ad_soyad ?: $k->kullanici }}
            </option>
          @endforeach
        </select>
      </div>
    @endif

    <div class="col-12">
      <label class="form-label" for="aciklama">Açıklama</label>
      <textarea class="form-control" id="aciklama" name="aciklama" rows="3"
                maxlength="5000">{{ old('aciklama', $firsat->aciklama) }}</textarea>
    </div>
  </div>

  @if($ozelAlanlar->isNotEmpty())
    <hr class="my-4" style="border-color:var(--cizgi)">
    <h6 style="color:var(--ap);font-size:.75rem;letter-spacing:1.4px;text-transform:uppercase">Ek Bilgiler</h6>

    <div class="row g-3 mt-1">
      @foreach($ozelAlanlar as $alan)
        @include('panel.ortak.ozel-alan', [
          'alan'   => $alan,
          'mevcut' => old('ozel.' . $alan->anahtar, $ozelDegerler[$alan->anahtar] ?? ''),
        ])
      @endforeach
    </div>
  @endif

  <div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Kaydet</button>
    <a href="{{ route('panel.firsat.pano') }}" class="btn btn-outline-secondary">İptal</a>

    @if($firsat->exists && auth()->user()->yoneticiMi())
      <button type="submit" form="silFormu" class="btn btn-outline-danger ms-auto"
              onclick="return confirm('Bu fırsat silinecek. Emin misiniz?')">
        <i class="bi bi-trash me-1"></i> Sil
      </button>
    @endif
  </div>
</form>

{{-- Silme formu ana formun DIŞINDA: iç içe <form> geçersizdir ve
     içindeki _method=delete dış forma karışıp "Kaydet"i silmeye çevirir. --}}
@if($firsat->exists && auth()->user()->yoneticiMi())
  <form id="silFormu" method="post" action="{{ route('panel.firsat.sil', $firsat) }}" class="d-none">
    @csrf @method('delete')
  </form>
@endif

@endsection

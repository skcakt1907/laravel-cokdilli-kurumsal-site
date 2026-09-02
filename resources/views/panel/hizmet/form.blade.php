@extends('panel.layout')
@section('baslik', $hizmet->exists ? 'Faaliyet Alanını Düzenle' : 'Yeni Faaliyet Alanı')

@section('icerik')

<form method="post" enctype="multipart/form-data" novalidate
      action="{{ $hizmet->exists ? route('panel.hizmet.guncelle', $hizmet) : route('panel.hizmet.kaydet') }}">
  @csrf
  @if($hizmet->exists) @method('put') @endif

  <div class="card p-4">
    <div class="row g-3">

      @include('panel.ortak.dilli-alan', [
        'ad' => 'baslik', 'etiket' => 'Başlık', 'kayit' => $hizmet, 'zorunlu' => true,
      ])

      <div class="col-md-6">
        <label class="form-label" for="f_grup">Sektör Ailesi <span style="color:var(--ap)">*</span></label>
        <input type="text" class="form-control" id="f_grup" name="grup" list="gruplar"
               value="{{ old('grup', $hizmet->grup) }}" required maxlength="100">
        <datalist id="gruplar">
          @foreach($gruplar as $grup)<option value="{{ $grup }}">@endforeach
        </datalist>
        <div class="form-text">Menüde ve anasayfada bu başlık altında gruplanır.</div>

        {{-- Grup çevirileri: aktif dil sayısı kadar alan üretilir --}}
        @foreach(\App\Support\Dil::cevrilecek() as $dKod => $dTanim)
          <label class="form-label mt-2" for="f_grup_{{ $dKod }}">
            Sektör Ailesi <span class="en-hint">{{ strtoupper($dKod) }}</span>
          </label>
          <input type="text" class="form-control" id="f_grup_{{ $dKod }}"
                 name="ceviri[{{ $dKod }}][grup]" maxlength="100"
                 @if(($dTanim['yon'] ?? 'ltr') === 'rtl') dir="rtl" @endif
                 value="{{ old("ceviri.{$dKod}.grup", $hizmet->exists ? $hizmet->ceviri('grup', $dKod) : '') }}">
        @endforeach
      </div>

      @include('panel.ortak.dilli-alan', [
        'ad' => 'ozet', 'etiket' => 'Özet', 'kayit' => $hizmet, 'tip' => 'uzun', 'satir' => 2,
        'ipucu' => 'Kartlarda ve arama sonuçlarında görünür.',
      ])

      @include('panel.ortak.dilli-alan', [
        'ad' => 'icerik', 'etiket' => 'İçerik', 'kayit' => $hizmet, 'tip' => 'uzun', 'satir' => 10,
        'ipucu' => 'Boş satır yeni paragraf açar. "- " ile madde, "## " ile ara başlık yazılır.',
      ])

      <div class="col-md-3">
        <label class="form-label" for="f_ikon">İkon</label>
        <input type="text" class="form-control" id="f_ikon" name="ikon"
               value="{{ old('ikon', $hizmet->ikon) }}" maxlength="60" placeholder="bi-fuel-pump">
        <div class="form-text">Bootstrap Icons adı.</div>
      </div>

      <div class="col-md-3">
        <label class="form-label" for="f_sira">Sıra</label>
        <input type="number" class="form-control" id="f_sira" name="sira" min="0" max="999"
               value="{{ old('sira', $hizmet->sira ?? 0) }}">
      </div>

      @include('panel.ortak.gorsel-alan', [
        'kayit' => $hizmet, 'etiket' => 'Detay Görseli',
      ])

      <div class="col-12">
        <div class="form-check">
          <input type="hidden" name="durum" value="0">
          <input class="form-check-input" type="checkbox" name="durum" value="1" id="f_durum"
                 @checked(old('durum', $hizmet->exists ? $hizmet->durum : true))>
          <label class="form-check-label" for="f_durum">Sitede yayında</label>
        </div>

        @if($hizmet->exists)
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="slug_yenile" value="1" id="f_slug">
            <label class="form-check-label" for="f_slug" style="font-size:.86rem">
              Adresi başlığa göre yenile
            </label>
          </div>
          <div class="form-text">
            Şu anki adres: <code>/faaliyet-alanlari/{{ $hizmet->slug }}</code> —
            değiştirirseniz eski bağlantılar çalışmaz.
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Kaydet</button>
    <a href="{{ route('panel.hizmet.index') }}" class="btn btn-outline-secondary">İptal</a>

    @if($hizmet->exists)
      {{-- Buton burada duruyor ama asıl form aşağıda, ana formun DIŞINDA. --}}
      <button type="submit" form="silFormu" class="btn btn-outline-danger ms-auto"
              onclick="return confirm('{{ $hizmet->baslik }} silinecek. Emin misiniz?')">
        <i class="bi bi-trash me-1"></i> Sil
      </button>
    @endif
  </div>
</form>

{{--
  Silme formu ana formun DIŞINDA olmak zorunda.
  İç içe <form> geçersiz HTML'dir: tarayıcı iç etiketi yok sayar ve
  içindeki @method('delete') alanı dış forma karışır. O zaman formda
  iki _method olur, PHP sonuncuyu alır ve "Kaydet" kaydı SİLER.
--}}
@if($hizmet->exists)
  <form id="silFormu" method="post" action="{{ route('panel.hizmet.sil', $hizmet) }}" class="d-none">
    @csrf @method('delete')
  </form>
@endif

@endsection

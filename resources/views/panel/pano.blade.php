@extends('panel.layout')
@section('baslik', 'Pano')

@section('icerik')

@php($kullanici = auth()->user())

{{-- ===== SAYAÇ KARTLARI ===== --}}
@php($kartlar = [
  ['bi-people',        'Müşteri',       $sayilar['musteri'],     route('panel.musteri.index')],
  ['bi-kanban',        'Açık Fırsat',   $sayilar['acik_firsat'], route('panel.firsat.pano')],
  ['bi-calendar-plus', 'Bu Ay Eklenen', $sayilar['bu_ay'],       null],
  ['bi-person-dash',   'Sorumlusu Yok', $sayilar['sahipsiz'],    route('panel.musteri.index')],
])

<div class="row g-3 mb-4">
  @foreach($kartlar as [$ikon, $etiket, $deger, $adres])
    <div class="col-lg-3 col-md-6">
      @if($adres)<a href="{{ $adres }}" class="text-decoration-none">@endif
        <div class="stat-card">
          <div class="icon"><i class="bi {{ $ikon }}"></i></div>
          <div>
            <h3>{{ $deger }}</h3>
            <p>{{ $etiket }}</p>
          </div>
        </div>
      @if($adres)</a>@endif
    </div>
  @endforeach
</div>

<div class="row g-4">

  {{-- ===== SON MÜŞTERİLER ===== --}}
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Son Müşteriler</span>
        <a href="{{ route('panel.musteri.index') }}" class="btn btn-sm btn-outline-secondary">Tümü</a>
      </div>

      @if($sonMusteriler->isEmpty())
        <div class="card-body text-center" style="color:var(--metin-sonuk)">
          Henüz müşteri kaydı yok. Siteden gelen iletişim mesajları buraya aday olarak düşer.
        </div>
      @else
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Firma / Kişi</th><th>Kaynak</th><th>Sorumlu</th><th>Tarih</th></tr></thead>
            <tbody>
              @foreach($sonMusteriler as $musteri)
                <tr>
                  <td>
                    <a href="{{ route('panel.musteri.detay', $musteri) }}" class="fw-semibold">
                      {{ $musteri->adi }}
                    </a>
                  </td>
                  <td style="font-size:.84rem">{{ $musteri->kaynak ?: '—' }}</td>
                  <td style="font-size:.84rem">{{ $musteri->sorumlu?->ad_soyad ?? 'Atanmamış' }}</td>
                  <td style="font-size:.82rem">{{ $musteri->created_at?->format('d.m.Y') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>

  {{-- ===== AÇIK FIRSATLARIN DEĞERİ ===== --}}
  <div class="col-lg-5">
    @if($boruHacmi->isNotEmpty())
      <div class="card">
        <div class="card-header">Açık Fırsatların Değeri</div>
        <div class="list-group list-group-flush">
          @foreach($boruHacmi as $birim => $toplam)
            <div class="list-group-item d-flex justify-content-between">
              <span style="color:var(--metin-sonuk)">{{ $birim }}</span>
              <strong style="color:var(--ap)">{{ number_format((float) $toplam, 0, ',', '.') }}</strong>
            </div>
          @endforeach
        </div>
        <div class="card-body pt-2" style="font-size:.76rem;color:var(--metin-sonuk)">
          Kurlar bilinmediği için para birimleri ayrı gösteriliyor.
        </div>
      </div>
    @else
      <div class="card">
        <div class="card-header">Açık Fırsatların Değeri</div>
        <div class="card-body text-center" style="color:var(--metin-sonuk)">
          Henüz açık fırsat yok.
          <div class="mt-2">
            <a href="{{ route('panel.firsat.olustur') }}" class="btn btn-sm btn-primary">
              <i class="bi bi-plus-lg me-1"></i> Fırsat Ekle
            </a>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>

@unless($kullanici->yoneticiMi())
  <p class="mt-4" style="color:var(--metin-sonuk);font-size:.8rem">
    <i class="bi bi-info-circle me-1"></i>
    Temsilci olarak yalnızca size atanmış kayıtları görüyorsunuz.
  </p>
@endunless

@endsection

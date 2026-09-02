{{--
  Şirket / iş ortağı kartı. Liste sayfası ve detay sayfasının "diğerleri"
  bölümü aynı kartı kullanır. Beklenen: $ortak (Proje)
--}}
<div class="col-lg-4 col-md-6">
  <a class="company-card h-100 d-block" href="{{ route('site.ortak', $ortak->slug) }}">
    @if($ortak->gorsel)
      <div class="cc-img">
        <img src="@gorsel($ortak->gorsel)" alt="{{ $ortak->d('baslik') }}" loading="lazy">
      </div>
    @else
      {{-- Logosu olmayan kayıtta baş harfler gösterilir --}}
      <div class="cc-img cc-mono">
        {{ mb_strtoupper(mb_substr($ortak->d('baslik'), 0, 2)) }}
      </div>
    @endif

    <div class="p-3">
      <h5>{{ $ortak->d('baslik') }}</h5>

      @if($ortak->d('kategori'))
        <span class="cc-sector">{{ $ortak->d('kategori') }}</span>
      @endif

      <div class="cc-links mt-2">
        @if($ortak->d('ulke'))
          <span><i class="bi bi-geo-alt me-1"></i>{{ $ortak->d('ulke') }}</span>
        @endif
        @if($ortak->tarih)
          <span class="ms-2"><i class="bi bi-calendar3 me-1"></i>{{ $ortak->tarih }}</span>
        @endif
      </div>
    </div>
  </a>
</div>

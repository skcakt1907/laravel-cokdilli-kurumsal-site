@extends('site.layout')

@php
  use App\Models\Ayar;
  use App\Support\Metin;
@endphp

@section('icerik')

<section class="hero">
  <div class="container">
    <div class="hero-grid">
      <div class="hero-metin">
        <span class="hero-badge"><i class="bi bi-circle-fill"></i> {{ Ayar::dilli('sektor_seridi') }}</span>
        <h1>{{ Ayar::al('site_adi') }}</h1>
        <p class="hero-slogan">{{ Ayar::dilli('slogan') }}</p>
        <p class="hero-aciklama">{{ Ayar::dilli('hero_alt') }}</p>
        <div class="hero-cta">
          <a href="{{ route('site.kurumsal') }}" class="btn btn-orange me-md-2">@lang('site.biz_kimiz')</a>
          <a href="{{ route('site.faaliyetler') }}" class="btn btn-line">
            @lang('site.nav_faaliyet') <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>
      </div>
      <div class="hero-amblem">
        <img src="{{ Metin::logoUrl() }}" class="marka-logo" alt="{{ Ayar::al('resmi_unvan') }}">
      </div>
    </div>
  </div>
</section>

{{-- ===== BİZ KİMİZ ===== --}}
<section>
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5">
        <div class="about-img-wrap">
          <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=900&q=80"
               alt="{{ Ayar::al('site_adi') }}">
        </div>
      </div>
      <div class="col-lg-7">
        <div class="section-head" style="margin-bottom:1.1rem">
          <span class="mini">@lang('site.nav_kurumsal')</span>
          <h2>@lang('site.biz_kimiz')</h2>
        </div>
        <p class="lead" style="color:var(--gray)">{{ Ayar::dilli('hakkimizda_kisa') }}</p>
        <div class="rich-text">@zengin(\App\Models\Ayar::dilli('hakkimizda_uzun'))</div>
        <a href="{{ route('site.kurumsal') }}" class="btn btn-orange mt-3">
          @lang('site.devamini_oku') <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>
    </div>
  </div>
</section>

{{-- ===== BAŞKANIN MESAJI =====
     Bir üstteki "Biz Kimiz" bölümüyle aynı sınıfları kullanır; tek fark
     flex-lg-row-reverse ile görselin sağa alınması. Mesaj girilmemişse
     bölüm hiç basılmaz. --}}
@php
  $bmMetin = Ayar::dilli('baskan_mesaji');

  // Özet: baştan itibaren paragrafları birleştir. Başlık (#) ve madde (-)
  // satırları atlanır. İlk paragraf çoğu zaman tek cümlelik selamlama olduğu
  // için ~200 karaktere ulaşana kadar devam edilir.
  $bmOzet = '';
  foreach (preg_split("/\n\s*\n/", trim((string) $bmMetin)) as $bmParca) {
      $bmParca = trim(strip_tags($bmParca));
      if ($bmParca === '' || str_starts_with($bmParca, '#') || str_starts_with($bmParca, '-')) {
          continue;
      }
      $bmOzet = trim($bmOzet . ' ' . $bmParca);
      if (mb_strlen($bmOzet) >= 200) {
          break;
      }
  }
@endphp

@if($bmMetin && $bmOzet)
  <section>
    <div class="container">
      <div class="row align-items-center g-5 flex-lg-row-reverse">

        @if($baskan && $baskan->foto)
          <div class="col-lg-5">
            <div class="about-img-wrap">
              <img src="@gorsel($baskan->foto)" alt="{{ $baskan->ad }}">
            </div>
          </div>
        @endif

        <div class="{{ $baskan && $baskan->foto ? 'col-lg-7' : 'col-12' }}">
          <div class="section-head" style="margin-bottom:1.1rem">
            <span class="mini">{{ Ayar::dilli('baskan_mesaj_alt') }}</span>
            <h2>{{ Ayar::dilli('baskan_mesaj_baslik') }}</h2>
          </div>

          <p class="lead" style="color:var(--gray)">
            {{ \Illuminate\Support\Str::limit($bmOzet, 320) }}
          </p>

          @if($baskan)
            <div style="margin-top:1.2rem">
              <strong>{{ $baskan->ad }}</strong>
              <div style="font-size:.87rem;color:var(--gray)">{{ $baskan->d('unvan') }}</div>
            </div>
          @endif

          <a href="{{ route('site.baskan') }}" class="btn btn-orange mt-3">
            @lang('site.devamini_oku') <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>
      </div>
    </div>
  </section>
@endif

{{-- ===== TEMEL YAKLAŞIM ===== --}}
@if(Ayar::dilli('yaklasim'))
  <div class="formula-strip">
    <div class="container">
      <span class="fs-label">@lang('site.yaklasim_baslik')</span>
      <div class="fs-parts">
        @foreach(array_map('trim', explode('+', Ayar::dilli('yaklasim'))) as $i => $parca)
          @if($i > 0)<span class="fs-plus">+</span>@endif
          <span class="fs-part">{{ $parca }}</span>
        @endforeach
      </div>
    </div>
  </div>
@endif

{{-- ===== NELER YAPARIZ ===== --}}
@if($aileler->isNotEmpty())
  <section class="services-grid">
    <div class="container">
      <div class="section-head center">
        <span class="mini">@lang('site.faaliyet_alt')</span>
        <h2>@lang('site.ne_yapariz')</h2>
      </div>
      <div class="row g-4">
        @foreach($aileler as $aile)
          @php($gosterilecek = $aile['alanlar']->take(4))
          @php($kalan = $aile['alanlar']->count() - $gosterilecek->count())

          <div class="col-lg-4 col-md-6">
            <a class="family-card" href="{{ route('site.faaliyetler') }}#{{ $aile['slug'] }}">
              <div class="fc-head">
                <div class="ac-icon"><i class="bi {{ $aile['ikon'] }}"></i></div>
                <div>
                  <h4>{{ $aile['ad'] }}</h4>
                  <span class="fc-adet">{{ $aile['alanlar']->count() }} @lang('site.sayac_sektor')</span>
                </div>
              </div>
              <ul class="fc-list">
                @foreach($gosterilecek as $alan)
                  <li>{{ $alan->d('baslik') }}</li>
                @endforeach
                @if($kalan > 0)
                  <li class="fc-daha">+{{ $kalan }} @lang('site.daha')</li>
                @endif
              </ul>
              <span class="ac-link">@lang('site.incele') <i class="bi bi-arrow-right"></i></span>
            </a>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endif

{{-- ===== NEDEN FGG ===== --}}
@if($nedenler->isNotEmpty())
  <section>
    <div class="container">
      <div class="section-head center">
        <span class="mini">@lang('site.neden_alt')</span>
        <h2>@lang('site.neden_baslik')</h2>
      </div>
      <div class="row g-4">
        @foreach($nedenler as $neden)
          <div class="col-lg-4 col-md-6">
            <div class="vm-card h-100">
              <i class="bi {{ $neden->ikon }}"></i>
              <h4>{{ $neden->d('baslik') }}</h4>
              <p>{{ $neden->d('ozet') }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endif

{{-- ===== PROJE GELİŞTİRME MODELİ ===== --}}
@if($surec->isNotEmpty())
  <section style="background:var(--light)">
    <div class="container">
      <div class="section-head center">
        <span class="mini">@lang('site.surec_alt')</span>
        <h2>@lang('site.surec_baslik')</h2>
      </div>
      <div class="surec-grid">
        @foreach($surec as $adim)
          <div class="surec-item">
            <span class="sr-no">{{ $adim->etiket }}</span>
            <div>
              <h5>{{ $adim->d('baslik') }}</h5>
              <p>{{ $adim->d('ozet') }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endif

{{-- ===== ULUSLARARASI İŞ AĞI ===== --}}
@if($bolgeler->isNotEmpty())
  <section class="bolge-band">
    <div class="container">
      <div class="section-head center">
        <span class="mini">@lang('site.bolge_alt')</span>
        <h2>@lang('site.bolge_baslik')</h2>
      </div>
      <div class="bolge-grid">
        @foreach($bolgeler as $bolge)
          <div class="bolge-item">
            <i class="bi {{ $bolge->ikon }}"></i>
            <h5>{{ $bolge->d('baslik') }}</h5>
            <span>{{ $bolge->d('ozet') }}</span>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endif

{{-- ===== RAKAMLARLA ===== --}}
@if(count($sayaclar))
  <section class="stats">
    <div class="container">
      <div class="row g-4 justify-content-center">
        @foreach($sayaclar as $sayac)
          <div class="col-md-3 col-6">
            <div class="stat">
              <i class="bi {{ $sayac['ikon'] }}"></i>
              <div><h3>{{ $sayac['deger'] }}</h3><p>{{ $sayac['etiket'] }}</p></div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endif

{{-- ===== HABERLER ===== --}}
@if($haberler->isNotEmpty())
  <section style="background:var(--light)">
    <div class="container">
      <div class="section-head center">
        <span class="mini">{{ Ayar::al('site_adi') }}</span>
        <h2>@lang('site.nav_haberler')</h2>
      </div>
      <div class="row g-4">
        @foreach($haberler as $haber)
          <div class="col-lg-4 col-md-6">
            <div class="blog-card">
              <div class="img">
                <img src="@gorsel($haber->gorsel)" alt="{{ $haber->d('baslik') }}">
                @if($haber->d('kategori'))<span class="cat">{{ $haber->d('kategori') }}</span>@endif
              </div>
              <div class="blog-body">
                <div class="meta"><i class="bi bi-calendar3"></i>@tarih($haber->tarih)</div>
                <h5><a href="{{ route('site.haber', $haber->slug) }}">{{ $haber->d('baslik') }}</a></h5>
                <p>{{ $haber->d('ozet') }}</p>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endif

<section class="cta-strip">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <h3>@lang('site.bize_ulasin')</h3>
        <p>{{ Ayar::dilli('kapanis') }}</p>
      </div>
      <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <a href="{{ route('site.iletisim') }}" class="btn">
          @lang('site.nav_iletisim') <i class="bi bi-arrow-right ms-2"></i>
        </a>
      </div>
    </div>
  </div>
</section>

@endsection

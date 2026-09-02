@extends('site.layout')

@php use App\Models\Ayar; @endphp

@section('icerik')

@include('site.sayfa-basi', [
  'baslik'     => __('site.nav_kurumsal'),
  'kirintilar' => [__('site.nav_kurumsal') => null],
])

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
          <span class="mini">{{ Ayar::al('site_adi') }}</span>
          <h2>@lang('site.biz_kimiz')</h2>
        </div>
        <p class="lead" style="color:var(--gray)">{{ Ayar::dilli('hakkimizda_kisa') }}</p>
        <div class="rich-text">@zengin(\App\Models\Ayar::dilli('hakkimizda_uzun'))</div>

        @if(Ayar::dilli('baskan_mesaji'))
          <a href="{{ route('site.baskan') }}" class="btn btn-orange mt-3">
            {{ Ayar::dilli('baskan_mesaj_baslik') }} <i class="bi bi-arrow-right ms-1"></i>
          </a>
        @endif
      </div>
    </div>
  </div>
</section>

{{-- ===== VİZYON / MİSYON ===== --}}
@if(Ayar::dilli('vizyon') || Ayar::dilli('misyon'))
  <section style="background:var(--light)">
    <div class="container">
      <div class="row g-4">
        @if(Ayar::dilli('vizyon'))
          <div class="col-lg-6">
            <div class="vm-card h-100">
              <span class="mini d-block mb-1"
                    style="color:var(--accent);font-weight:700;letter-spacing:1.6px;text-transform:uppercase;font-size:.72rem">
                @lang('site.vizyon')
              </span>
              <h4>{{ Ayar::dilli('vizyon_baslik') }}</h4>
              <div class="rich-text mt-2">@zengin(\App\Models\Ayar::dilli('vizyon'))</div>
            </div>
          </div>
        @endif

        @if(Ayar::dilli('misyon'))
          <div class="col-lg-6">
            <div class="vm-card h-100">
              <span class="mini d-block mb-1"
                    style="color:var(--accent);font-weight:700;letter-spacing:1.6px;text-transform:uppercase;font-size:.72rem">
                @lang('site.misyon')
              </span>
              <h4>@lang('site.misyon')</h4>
              <div class="rich-text mt-2">@zengin(\App\Models\Ayar::dilli('misyon'))</div>
            </div>
          </div>
        @endif
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

{{-- ===== YÖNETİM KADROSU ===== --}}
@if($yoneticiler->isNotEmpty())
  <section>
    <div class="container">
      <div class="section-head center">
        <span class="mini">{{ Ayar::al('site_adi') }}</span>
        <h2>@lang('site.yonetim')</h2>
      </div>
      <div class="row g-4 justify-content-center">
        @foreach($yoneticiler as $yonetici)
          <div class="col-lg-4 col-md-6">
            <a class="team-card d-block" href="{{ route('site.yonetici', $yonetici->id) }}">
              @if($yonetici->foto)
                <img src="@gorsel($yonetici->foto)" alt="{{ $yonetici->ad }}" class="w-100">
              @endif
              <div class="tb">
                <h6>{{ $yonetici->ad }}</h6>
                <span>{{ $yonetici->d('unvan') }}</span>
              </div>
            </a>
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

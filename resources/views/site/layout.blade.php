@php
  use App\Models\Ayar;
  use App\Support\Metin;

  $baslik   = $pageTitle ?? Ayar::dilli('site_baslik');
  $aciklama = $pageDesc  ?? Ayar::dilli('site_aciklama');
@endphp
<!doctype html>
@php($sagdanSola = \App\Support\Dil::sagdanSolaMi())
<html lang="{{ app()->getLocale() }}" dir="{{ \App\Support\Dil::yon() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $baslik }}</title>
<meta name="description" content="{{ $aciklama }}">
<link rel="canonical" href="{{ url()->current() }}">

@foreach(\App\Http\Middleware\DilSec::diller() as $kod => $ad)
  <link rel="alternate" hreflang="{{ $kod }}" href="{{ request()->fullUrlWithQuery(['lang' => $kod]) }}">
@endforeach

<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ Ayar::al('site_adi') }}">
<meta property="og:title" content="{{ $baslik }}">
<meta property="og:description" content="{{ $aciklama }}">
<meta property="og:image" content="{{ asset('img/logo-fgg.png') }}">
@include('parcalar.simge')

{{-- Site fontu: Montserrat (müşteri talebi). Panel ayrı, Inter kullanmaya devam eder.
     ÖNEMLİ: Montserrat'ta Arap alfabesi YOK. Arapça/Farsça'da tarayıcı rastgele
     bir sistem fontuna düşer ve metin özensiz görünür — o yüzden bu dillerde
     kendi alfabesi için tasarlanmış font ayrıca yükleniyor. --}}
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

@if(app()->getLocale() === 'ar')
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
@elseif(app()->getLocale() === 'fa')
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700&display=swap" rel="stylesheet">
@endif

@stack('font')

{{-- Bootstrap'in RTL sürümü: ızgara, ms-/me- boşlukları ve hizalama
     yardımcıları kendiliğinden döner; elle çevrilecek iş azalır. --}}
@if($sagdanSola)
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
@else
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
@endif
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
<link href="{{ asset('css/style.css') }}?v={{ @filemtime(public_path('css/style.css')) }}" rel="stylesheet">
@stack('stil')
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="{{ route('site.anasayfa') }}">
      <img src="{{ Metin::logoUrl() }}" class="marka-logo" alt="{{ Ayar::al('site_adi') }}">
      <span class="brand-lockup">
        <strong>FGG HOLDING</strong>
        <small>{{ Ayar::al('resmi_unvan') }}</small>
      </span>
    </a>

    <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Menü">
      <i class="bi bi-list" style="font-size:1.8rem"></i>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link @if(request()->routeIs('site.anasayfa')) active @endif"
             href="{{ route('site.anasayfa') }}">@lang('site.nav_anasayfa')</a>
        </li>
        {{--
          Başkanın mesajı girilmişse Kurumsal bir açılır menüye dönüşür.
          Düz .dropdown-panel (mega değil) — stili style.css:44'te hazır.
        --}}
        @php($baskanMesajiVar = (bool) Ayar::dilli('baskan_mesaji'))

        <li class="nav-item @if($baskanMesajiVar) nav-dropdown @endif">
          <a class="nav-link @if(request()->routeIs('site.kurumsal') || request()->routeIs('site.baskan')) active @endif"
             href="{{ route('site.kurumsal') }}">
            @lang('site.nav_kurumsal')
            @if($baskanMesajiVar)<i class="bi bi-chevron-down ms-1" style="font-size:.7rem"></i>@endif
          </a>

          @if($baskanMesajiVar)
            <div class="dropdown-panel">
              <a href="{{ route('site.kurumsal') }}">
                <i class="bi bi-building"></i><span>@lang('site.nav_kurumsal')</span>
              </a>
              <a href="{{ route('site.baskan') }}">
                <i class="bi bi-chat-quote"></i><span>{{ Ayar::dilli('baskan_mesaj_baslik') }}</span>
              </a>
            </div>
          @endif
        </li>

        {{-- nav-mega: geniş panel bu <li>'ye değil navbar'a hizalansın diye
             (bkz. style.css "MEGA MENÜ HİZALAMASI"). Kurumsal menüsündeki
             dar panel bu sınıfı ALMAZ, o kendi <li>'sine hizalanır. --}}
        <li class="nav-item nav-dropdown nav-mega">
          <a class="nav-link @if(request()->routeIs('site.faaliyet*')) active @endif"
             href="{{ route('site.faaliyetler') }}">
            @lang('site.nav_faaliyet') <i class="bi bi-chevron-down ms-1" style="font-size:.7rem"></i>
          </a>

          @if($menuFaaliyetler->isNotEmpty())
            <div class="dropdown-panel mega">
              <div class="mega-grid">
                @foreach($menuFaaliyetler as $grupAdi => $alanlar)
                  <div class="mega-col">
                    <h6>
                      <a href="{{ route('site.faaliyetler') }}#{{ Metin::slug($grupAdi) }}">
                        {{ $grupAdi }}<span class="mg-adet">{{ $alanlar->count() }}</span>
                      </a>
                    </h6>
                    @foreach($alanlar as $alan)
                      <a href="{{ route('site.faaliyet', $alan->slug) }}">
                        <i class="bi {{ $alan->ikon }}"></i><span>{{ $alan->d('baslik') }}</span>
                      </a>
                    @endforeach
                  </div>
                @endforeach
              </div>
              <a class="mega-tumu" href="{{ route('site.faaliyetler') }}">
                @lang('site.tum_sektorler') <i class="bi bi-arrow-right"></i>
              </a>
            </div>
          @endif
        </li>

        <li class="nav-item">
          <a class="nav-link @if(request()->routeIs('site.ortaklar') || request()->routeIs('site.ortak')) active @endif"
             href="{{ route('site.ortaklar') }}">@lang('site.nav_ortaklar')</a>
        </li>

        @if($haberVar)
          <li class="nav-item">
            <a class="nav-link @if(request()->routeIs('site.haber*')) active @endif"
               href="{{ route('site.haberler') }}">@lang('site.nav_haberler')</a>
          </li>
        @endif

        <li class="nav-item dropdown nav-dil">
          <button class="dil-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-globe2"></i><span>{{ strtoupper(app()->getLocale()) }}</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end dil-menu">
            @foreach(\App\Http\Middleware\DilSec::diller() as $kod => $ad)
              <li>
                <a class="dropdown-item @if(app()->getLocale() === $kod) active @endif"
                   href="{{ request()->fullUrlWithQuery(['lang' => $kod]) }}" hreflang="{{ $kod }}">
                  <span class="dil-kod">{{ strtoupper($kod) }}</span>{{ $ad }}
                  @if(app()->getLocale() === $kod)<i class="bi bi-check2 ms-auto"></i>@endif
                </a>
              </li>
            @endforeach
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link nav-cta @if(request()->routeIs('site.iletisim')) active @endif"
             href="{{ route('site.iletisim') }}">@lang('site.nav_iletisim')</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

@yield('icerik')

@include('site.footer')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/main.js') }}?v={{ @filemtime(public_path('js/main.js')) }}"></script>
@stack('betik')
</body>
</html>

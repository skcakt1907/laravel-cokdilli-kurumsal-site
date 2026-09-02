@extends('site.layout')

@php use App\Models\Ayar; @endphp

{{-- Cormorant Garamond yalnızca bu sayfada yüklenir --}}
@push('font')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&display=swap"
      rel="stylesheet">
@endpush

@push('stil')
<style>
  .baskan-metin{ font-family:'Cormorant Garamond',Georgia,serif; font-size:1.16rem; line-height:1.85; }
  .baskan-metin p{ margin-bottom:1.25rem; color:var(--metin); }
  .baskan-metin .metin-baslik{
    font-family:'Montserrat',sans-serif; font-size:.78rem; letter-spacing:1.8px;
    text-transform:uppercase; color:var(--accent); margin:2.4rem 0 .9rem; font-weight:700;
  }
  .baskan-metin .metin-liste{ list-style:none; padding-left:0; margin:0 0 1.25rem; }
  .baskan-metin .metin-liste li{ position:relative; padding-left:1.3rem; margin-bottom:.4rem; }
  .baskan-metin .metin-liste li::before{
    content:""; position:absolute; left:0; top:.72em;
    width:6px; height:6px; border-radius:50%; background:var(--accent);
  }
  .baskan-giris{
    font-family:'Cormorant Garamond',Georgia,serif; font-style:italic;
    font-size:1.34rem; line-height:1.7; color:var(--accent-2,#e8c65a);
    border-left:3px solid var(--accent); padding-left:1.4rem; margin-bottom:2.2rem;
  }
  .baskan-imza{ margin-top:2.6rem; padding-top:1.6rem; border-top:1px solid var(--cizgi,rgba(212,175,55,.16)); }
  .baskan-imza .ad{ font-family:'Cormorant Garamond',Georgia,serif; font-size:1.5rem; color:#fff; }
  .baskan-imza .unvan{ font-size:.86rem; color:var(--metin-soluk,#948d81); margin-top:.2rem; }
  .baskan-foto{ width:100%; border-radius:14px; margin-bottom:1.2rem; }
</style>
@endpush

@section('icerik')

@include('site.sayfa-basi', [
  'baslik'     => Ayar::dilli('baskan_mesaj_baslik'),
  'kirintilar' => [
    __('site.nav_kurumsal')             => route('site.kurumsal'),
    Ayar::dilli('baskan_mesaj_baslik')  => null,
  ],
])

<section>
  <div class="container">
    <div class="row g-5 justify-content-center">

      <div class="col-lg-8">
        <div class="section-head" style="margin-bottom:1.4rem">
          <span class="mini">{{ Ayar::dilli('baskan_mesaj_alt') }}</span>
          <h2>{{ Ayar::dilli('baskan_mesaj_baslik') }}</h2>
        </div>

        {{-- CEO Digital Message — video girilmişse metnin üstünde --}}
        @include('parcalar.video', [
          'deger'  => Ayar::al('baskan_video'),
          'baslik' => Ayar::dilli('baskan_mesaj_baslik'),
        ])

        <div class="baskan-metin">@zengin($metin)</div>

        @if($baskan)
          <div class="baskan-imza">
            <div class="ad">{{ $baskan->ad }}</div>
            <div class="unvan">{{ $baskan->d('unvan') }}</div>
            <div class="unvan">{{ Ayar::al('resmi_unvan') }}</div>
          </div>
        @endif
      </div>

      <div class="col-lg-4">
        @if($baskan)
          <div class="vm-card">
            @if($baskan->foto)
              <img src="@gorsel($baskan->foto)" class="baskan-foto" alt="{{ $baskan->ad }}">
            @endif
            <h4 style="font-size:1.1rem">{{ $baskan->ad }}</h4>
            <p style="font-size:.87rem">{{ $baskan->d('unvan') }}</p>

            @if($baskan->d('ozgecmis'))
              <a href="{{ route('site.yonetici', $baskan->id) }}" class="ac-link">
                @lang('site.devamini_oku') <i class="bi bi-arrow-right"></i>
              </a>
            @endif

            @if($baskan->linkedin)
              <a href="{{ $baskan->linkedin }}" target="_blank" rel="noopener"
                 class="btn btn-line w-100 mt-3">
                <i class="bi bi-linkedin me-1"></i> LinkedIn
              </a>
            @endif
          </div>
        @endif

        <div class="vm-card text-center mt-4">
          <i class="bi bi-chat-dots"></i>
          <h4 style="font-size:1.05rem">@lang('site.bize_ulasin')</h4>
          <p class="mb-3">@lang('site.iletisim_alt')</p>
          <a href="{{ route('site.iletisim') }}" class="btn btn-orange w-100">@lang('site.nav_iletisim')</a>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection

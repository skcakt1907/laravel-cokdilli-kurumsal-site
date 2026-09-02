@php
  use App\Models\Ayar;
  use App\Models\Hizmet;
  use App\Support\Metin;

  // Sektör aileleri — 23 alanın tamamı yerine grup başlıkları
  $ayakGruplar = Hizmet::yayinda()->sirali()->get()
      ->unique(fn ($h) => $h->grup)
      ->values();

  $sosyalAglar = [
      'linkedin'  => 'bi-linkedin',
      'instagram' => 'bi-instagram',
      'facebook'  => 'bi-facebook',
      'twitter'   => 'bi-twitter-x',
      'youtube'   => 'bi-youtube',
  ];
@endphp

<footer>
  <div class="container">
    <div class="row g-4">

      <div class="col-lg-4 col-md-6">
        <div class="brand">
          <div class="f-lockup">
            <img src="{{ Metin::logoUrl() }}" class="marka-logo" alt="{{ Ayar::al('site_adi') }}">
            <span>
              <strong>FGG HOLDING</strong>
              @if(Ayar::al('resmi_unvan'))<small>{{ Ayar::al('resmi_unvan') }}</small>@endif
            </span>
          </div>

          @if(Ayar::al('marka_slogan'))
            <div class="f-marka-slogan">{{ Ayar::al('marka_slogan') }}</div>
          @endif

          <p class="about-text">{{ Ayar::dilli('hakkimizda_kisa') }}</p>

          <div class="social">
            @foreach($sosyalAglar as $anahtar => $ikon)
              @continue(!Ayar::al($anahtar))
              <a href="{{ Ayar::al($anahtar) }}" target="_blank" rel="noopener" aria-label="{{ $anahtar }}">
                <i class="bi {{ $ikon }}"></i>
              </a>
            @endforeach
          </div>
        </div>
      </div>

      <div class="col-lg-2 col-md-6">
        <h5>@lang('site.footer_kurumsal')</h5>
        <a href="{{ route('site.kurumsal') }}">@lang('site.nav_kurumsal')</a>
        @if(Ayar::dilli('baskan_mesaji'))
          <a href="{{ route('site.baskan') }}">{{ Ayar::dilli('baskan_mesaj_baslik') }}</a>
        @endif
        <a href="{{ route('site.faaliyetler') }}">@lang('site.nav_faaliyet')</a>
        <a href="{{ route('site.ortaklar') }}">@lang('site.nav_ortaklar')</a>
        @if($haberVar ?? false)
          <a href="{{ route('site.haberler') }}">@lang('site.nav_haberler')</a>
        @endif
        <a href="{{ route('site.iletisim') }}">@lang('site.nav_iletisim')</a>
      </div>

      <div class="col-lg-3 col-md-6">
        <h5>@lang('site.nav_faaliyet')</h5>
        @foreach($ayakGruplar as $grup)
          <a href="{{ route('site.faaliyetler') }}#{{ Metin::slug($grup->grup) }}">{{ $grup->d('grup') }}</a>
        @endforeach
      </div>

      <div class="col-lg-3 col-md-6">
        <h5>@lang('site.nav_iletisim')</h5>
        <ul class="f-contact p-0 m-0">
          @if(Ayar::dilli('adres'))
            <li><i class="bi bi-geo-alt-fill"></i><span>{{ Ayar::dilli('adres') }}</span></li>
          @endif
          @if(Ayar::al('telefon'))
            <li>
              <i class="bi bi-telephone-fill"></i>
              <a href="tel:{{ Metin::telRakam(Ayar::al('telefon')) }}" style="display:inline;padding:0">
                {{ Ayar::al('telefon') }}
              </a>
            </li>
          @endif
          @if(Ayar::al('whatsapp'))
            <li>
              <i class="bi bi-whatsapp"></i>
              <a href="https://wa.me/{{ Metin::telRakam(Ayar::al('whatsapp')) }}"
                 target="_blank" rel="noopener" style="display:inline;padding:0">
                {{ Ayar::al('whatsapp') }}
              </a>
            </li>
          @endif
          @if(Ayar::al('mail'))
            <li>
              <i class="bi bi-envelope-fill"></i>
              <a href="mailto:{{ Ayar::al('mail') }}" style="display:inline;padding:0">{{ Ayar::al('mail') }}</a>
            </li>
          @endif
          @if(Ayar::dilli('calisma_saati'))
            <li><i class="bi bi-clock-fill"></i><span>{{ Ayar::dilli('calisma_saati') }}</span></li>
          @endif
        </ul>
      </div>
    </div>

    <div class="f-bottom">
      <span>&copy; {{ date('Y') }} {{ Ayar::al('site_adi') }}. @lang('site.footer_haklar')</span>
      <span>Design &amp; Development
        <a href="https://ornek.com" target="_blank" rel="noopener">DN Kreatif</a>
      </span>
    </div>
  </div>
</footer>

@if(Ayar::al('whatsapp'))
  <a class="wa-float" href="https://wa.me/{{ Metin::telRakam(Ayar::al('whatsapp')) }}"
     target="_blank" rel="noopener" aria-label="WhatsApp">
    <i class="bi bi-whatsapp"></i>
  </a>
@endif

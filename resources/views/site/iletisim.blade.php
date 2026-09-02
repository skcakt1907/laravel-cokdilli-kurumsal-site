@extends('site.layout')

@php
  use App\Models\Ayar;
  use App\Support\Metin;

  $telefonlar  = Metin::etiketliListe(Ayar::dilli('telefonlar'));
  $departmanlar = Metin::etiketliListe(Ayar::dilli('departman_mailler'));
@endphp

@section('icerik')

@include('site.sayfa-basi', [
  'baslik'     => __('site.nav_iletisim'),
  'kirintilar' => [__('site.nav_iletisim') => null],
])

<section>
  <div class="container">

    <div class="section-head center">
      <span class="mini">@lang('site.iletisim_alt')</span>
      <h2>@lang('site.bize_ulasin')</h2>
      <p>@lang('site.iletisim_giris')</p>
    </div>

    {{-- ===== KÜNYE KUTULARI ===== --}}
    <div class="row g-4 mb-5">
      <div class="col-lg-3 col-md-6">
        <div class="contact-box h-100">
          <i class="bi bi-geo-alt-fill"></i>
          <h5>@lang('site.adres')</h5>
          <p>
            @if(Ayar::dilli('adres_etiket'))
              <span class="ct-etiket">{{ Ayar::dilli('adres_etiket') }}</span>
            @endif
            {{ Ayar::dilli('adres') }}
          </p>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <div class="contact-box h-100">
          <i class="bi bi-telephone-fill"></i>
          <h5>@lang('site.telefon')</h5>
          <p>
            @forelse($telefonlar as $tel)
              <span class="ct-etiket">{{ $tel['etiket'] }}</span>
              @if($tel['bayrak'] === 'wa')
                <a href="https://wa.me/{{ Metin::telRakam($tel['deger']) }}"
                   target="_blank" rel="noopener" style="color:inherit">
                  <i class="bi bi-whatsapp me-1"></i>{{ $tel['deger'] }}
                </a>
              @else
                <a href="tel:{{ Metin::telRakam($tel['deger']) }}" style="color:inherit">{{ $tel['deger'] }}</a>
              @endif
              <br>
            @empty
              {{-- Liste boşsa eski tekil alanlara düş --}}
              <a href="tel:{{ Metin::telRakam(Ayar::al('telefon')) }}" style="color:inherit">
                {{ Ayar::al('telefon') }}
              </a>
            @endforelse
          </p>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <div class="contact-box h-100">
          <i class="bi bi-envelope-fill"></i>
          <h5>@lang('site.eposta')</h5>
          <p>
            <a href="mailto:{{ Ayar::al('mail') }}" style="color:inherit">{{ Ayar::al('mail') }}</a>
          </p>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <div class="contact-box h-100">
          <i class="bi bi-clock-fill"></i>
          <h5>@lang('site.calisma_saati')</h5>
          <p>{{ Ayar::dilli('calisma_saati') }}</p>
        </div>
      </div>
    </div>

    {{-- ===== DEPARTMAN E-POSTALARI ===== --}}
    @if($departmanlar)
      <div class="section-head center" style="margin-bottom:1.4rem">
        <span class="mini">@lang('site.departman_alt')</span>
        <h2>@lang('site.departman_baslik')</h2>
      </div>

      <div class="departman-grid mb-5">
        @foreach($departmanlar as $dep)
          <a class="departman-kart" href="mailto:{{ $dep['deger'] }}">
            <i class="bi bi-envelope-at"></i>
            <span class="dk-etiket">{{ $dep['etiket'] }}</span>
            <span class="dk-mail">{{ $dep['deger'] }}</span>
          </a>
        @endforeach
      </div>
    @endif

    {{-- ===== FORM + HARİTA ===== --}}
    <div class="row g-5 align-items-start" id="iletisim-form">
      <div class="col-lg-6">
        <div class="section-head" style="margin-bottom:1.4rem">
          <span class="mini">@lang('site.form_gonder')</span>
          <h2>@lang('site.form_baslik')</h2>
        </div>

        @if(session('formBasarili'))
          <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>{{ session('formBasarili') }}
          </div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <ul class="mb-0">@foreach($errors->all() as $hata)<li>{{ $hata }}</li>@endforeach</ul>
          </div>
        @endif

        <form method="post" action="{{ route('site.iletisim.gonder') }}" novalidate>
          @csrf

          {{-- Bot tuzağı: gerçek ziyaretçi görmez, boş kalır --}}
          <div style="position:absolute;left:-9999px" aria-hidden="true">
            <label for="website">Website</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="ad">@lang('site.form_ad') *</label>
              <input type="text" class="form-control" id="ad" name="ad"
                     maxlength="100" required value="{{ old('ad') }}">
            </div>

            <div class="col-md-6">
              <label class="form-label" for="mail">@lang('site.form_mail') *</label>
              <input type="email" class="form-control" id="mail" name="mail"
                     maxlength="150" required value="{{ old('mail') }}">
            </div>

            <div class="col-md-6">
              <label class="form-label" for="tel">@lang('site.form_tel')</label>
              <input type="text" class="form-control" id="tel" name="tel"
                     maxlength="40" value="{{ old('tel') }}">
            </div>

            <div class="col-md-6">
              <label class="form-label" for="konu">@lang('site.form_konu')</label>
              <input type="text" class="form-control" id="konu" name="konu"
                     maxlength="200" value="{{ old('konu') }}">
            </div>

            {{-- Seçilen sektör ailesi mesajın hangi birime gideceğini belirler.
                 Sektör sayfasından gelindiyse ?birim=... ile ön seçili gelir. --}}
            @if(!empty($birimler))
              <div class="col-12">
                <label class="form-label" for="birim">@lang('site.form_birim')</label>
                <select class="form-select" id="birim" name="birim">
                  <option value="">@lang('site.form_birim_genel')</option>
                  {{-- değer: türkçe grup adı (yönlendirme anahtarı)
                       etiket: aktif dildeki karşılığı --}}
                  @foreach($birimler as $birimDeger => $birimEtiket)
                    <option value="{{ $birimDeger }}"
                      @selected(old('birim', $secili ?? null) === $birimDeger)>{{ $birimEtiket }}</option>
                  @endforeach
                </select>
                <small style="color:var(--gray);font-size:.82rem">@lang('site.form_birim_ipucu')</small>
              </div>
            @endif

            <div class="col-12">
              <label class="form-label" for="mesaj">@lang('site.form_mesaj') *</label>
              <textarea class="form-control" id="mesaj" name="mesaj" rows="5"
                        maxlength="3000" required>{{ old('mesaj') }}</textarea>
            </div>

            <div class="col-12">
              <button type="submit" class="btn btn-orange">
                @lang('site.form_gonder') <i class="bi bi-send ms-1"></i>
              </button>
            </div>
          </div>
        </form>
      </div>

      <div class="col-lg-6">
        @if(Ayar::al('harita_url'))
          <div style="border-radius:12px;overflow:hidden;min-height:420px">
            <iframe src="{{ Ayar::al('harita_url') }}" width="100%" height="420"
                    style="border:0" allowfullscreen loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="@lang('site.adres')"></iframe>
          </div>
        @else
          <div class="vm-card h-100">
            <i class="bi bi-geo-alt-fill"></i>
            <h4>@lang('site.adres')</h4>
            <p>{{ Ayar::dilli('adres') }}</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</section>

@endsection

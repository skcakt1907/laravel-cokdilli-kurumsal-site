<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
{{-- Panodaki sürükle-bırak isteği bu token'ı kullanır --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('baslik', 'Panel') — {{ \App\Models\Ayar::al('site_adi', 'FGG Holding') }}</title>
<meta name="robots" content="noindex,nofollow">

@include('parcalar.simge')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
<link href="{{ asset('css/panel.css') }}?v={{ @filemtime(public_path('css/panel.css')) }}" rel="stylesheet">
@stack('stil')
</head>
<body>

@php($kullanici = auth()->user())

{{--
  Yapı ve sınıf adları düz PHP sürümündeki panelle birebir aynı:
  aside.sidebar > .brand + .grp + a   /   main.main > .topbar + .content
  panel.css o markup için yazıldı, sapma stilleri bozar.
--}}
<aside class="sidebar">
  <div class="brand">{{ \App\Models\Ayar::al('site_adi', 'FGG') }} <span>Panel</span></div>

  <div class="grp">Genel</div>
  <a href="{{ route('panel.pano') }}" class="{{ request()->routeIs('panel.pano') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i><span>Pano</span>
  </a>

  <div class="grp">CRM</div>
  <a href="{{ route('panel.musteri.index') }}" class="{{ request()->routeIs('panel.musteri.*') ? 'active' : '' }}">
    <i class="bi bi-people"></i><span>Müşteriler</span>
  </a>
  <a href="{{ route('panel.firsat.pano') }}" class="{{ request()->routeIs('panel.firsat.*') ? 'active' : '' }}">
    <i class="bi bi-kanban"></i><span>Fırsat Panosu</span>
  </a>

  @if($kullanici->yoneticiMi())
    <div class="grp">İçerik</div>
    <a href="{{ route('panel.hizmet.index') }}" class="{{ request()->routeIs('panel.hizmet.*') ? 'active' : '' }}"><i class="bi bi-grid-1x2"></i><span>Faaliyet Alanları</span></a>
    <a href="{{ route('panel.proje.index') }}" class="{{ request()->routeIs('panel.proje.*') ? 'active' : '' }}"><i class="bi bi-diagram-3"></i><span>İş Ortakları</span></a>
    <a href="{{ route('panel.yazi.index') }}" class="{{ request()->routeIs('panel.yazi.*') ? 'active' : '' }}"><i class="bi bi-newspaper"></i><span>Haberler</span></a>

    <div class="grp">Yönetim</div>
    <a href="{{ route('panel.mesaj.index') }}" class="{{ request()->routeIs('panel.mesaj.*') ? 'active' : '' }}">
      <i class="bi bi-envelope"></i><span>Mesajlar</span>
      @if(($okunmamisMesaj ?? 0) > 0)<span class="badge">{{ $okunmamisMesaj }}</span>@endif
    </a>
    <a href="{{ route('panel.ayar.index') }}" class="{{ request()->routeIs('panel.ayar.*') ? 'active' : '' }}"><i class="bi bi-gear"></i><span>Ayarlar</span></a>
  @endif

  @if($kullanici->sahipMi())
    <div class="grp">Sistem</div>
    <a href="{{ route('panel.kullanici.index') }}" class="{{ request()->routeIs('panel.kullanici.*') ? 'active' : '' }}">
      <i class="bi bi-person-badge"></i><span>Kullanıcılar</span>
    </a>
    <a href="{{ route('panel.alan.index') }}" class="{{ request()->routeIs('panel.alan.*') ? 'active' : '' }}">
      <i class="bi bi-sliders"></i><span>Özel Alanlar</span>
    </a>
  @endif

  <div class="grp">&nbsp;</div>
  <a href="{{ route('site.anasayfa') }}" target="_blank"><i class="bi bi-eye"></i><span>Siteyi Görüntüle</span></a>
  <a href="#" onclick="event.preventDefault();document.getElementById('cikisFormu').submit()">
    <i class="bi bi-box-arrow-right"></i><span>Çıkış Yap</span>
  </a>
</aside>

<main class="main">
  <div class="topbar">
    <h1>@yield('baslik', 'Panel')</h1>
    <div class="user">
      <i class="bi bi-person-circle"></i>{{ $kullanici->ad_soyad ?: $kullanici->kullanici }}
      <span class="badge ms-2">{{ $kullanici->rolAdi() }}</span>
    </div>
  </div>

  <div class="content">
    @if(session('basarili'))
      <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('basarili') }}</div>
    @endif
    @if(session('hata'))
      <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('hata') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Form gönderilemedi:</strong>
        <ul class="mb-0 mt-1">@foreach($errors->all() as $hata)<li>{{ $hata }}</li>@endforeach</ul>
      </div>
    @endif

    @yield('icerik')
  </div>
</main>

<form method="post" action="{{ route('panel.cikis') }}" id="cikisFormu" class="d-none">@csrf</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('betik')
</body>
</html>

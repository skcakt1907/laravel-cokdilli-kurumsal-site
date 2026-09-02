<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Panel Girişi — {{ \App\Models\Ayar::al('site_adi', 'FGG Holding') }}</title>
<meta name="robots" content="noindex,nofollow">

{{-- Bu sayfanın kendi <head>'i var, panel.layout'u kullanmıyor. --}}
@include('parcalar.simge')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
<style>
  :root{ --ap:#d4af37; --ap-acik:#e8c65a; --ap-koyu:#b48a3f; --zemin:#0b0b0b; --yuzey:#141414; --cizgi:rgba(212,175,55,.18); --metin:#ddd7cc; }
  body{ background:var(--zemin); color:var(--metin); font-family:'Inter',system-ui,sans-serif;
        min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem; }
  .giris-kutu{ width:100%; max-width:400px; background:var(--yuzey); border:1px solid var(--cizgi);
               border-radius:16px; padding:2.4rem 2rem; box-shadow:0 24px 60px rgba(0,0,0,.6); }
  .marka{ text-align:center; margin-bottom:1.8rem; }
  .marka img{ height:52px; width:auto; margin-bottom:.8rem; }
  .marka strong{ display:block; color:#fff; font-size:1.1rem; letter-spacing:-.01em; }
  .marka small{ color:var(--ap); font-size:.7rem; letter-spacing:1.6px; text-transform:uppercase; font-weight:700; }
  .form-label{ color:var(--metin); font-size:.84rem; font-weight:600; }
  .form-control{ background:#1a1a1a; border:1px solid var(--cizgi); color:#fff; padding:.7rem .9rem; }
  .form-control:focus{ background:#1f1f1f; border-color:var(--ap); color:#fff; box-shadow:0 0 0 4px rgba(212,175,55,.14); }
  .btn-altin{ background:var(--ap); border:0; color:#0b0b0b; font-weight:700; padding:.7rem; width:100%; }
  .btn-altin:hover{ background:var(--ap-acik); color:#0b0b0b; }
  .alert-danger{ background:rgba(230,57,70,.12); border:1px solid rgba(230,57,70,.4); color:#f0b9bd; font-size:.87rem; }
  .form-check-input:checked{ background-color:var(--ap); border-color:var(--ap); }
  a.geri{ color:var(--ap); font-size:.82rem; text-decoration:none; }
</style>
</head>
<body>

<div class="giris-kutu">
  <div class="marka">
    <img src="{{ asset('img/logo-fgg-256.png') }}" alt="{{ \App\Models\Ayar::al('site_adi') }}"
         onerror="this.style.display='none'">
    <strong>{{ \App\Models\Ayar::al('site_adi', 'FGG Holding') }}</strong>
    <small>Yönetim Paneli</small>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger" role="alert">
      <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
    </div>
  @endif

  <form method="post" action="{{ route('panel.giris.gonder') }}" novalidate>
    @csrf

    <div class="mb-3">
      <label class="form-label" for="kullanici">Kullanıcı Adı</label>
      <input type="text" class="form-control" id="kullanici" name="kullanici"
             value="{{ old('kullanici') }}" required autofocus autocomplete="username">
    </div>

    <div class="mb-3">
      <label class="form-label" for="sifre">Şifre</label>
      <input type="password" class="form-control" id="sifre" name="sifre"
             required autocomplete="current-password">
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="hatirla" id="hatirla" value="1">
      <label class="form-check-label" for="hatirla" style="font-size:.85rem">Beni hatırla</label>
    </div>

    <button type="submit" class="btn btn-altin">
      <i class="bi bi-box-arrow-in-right me-1"></i> Giriş Yap
    </button>
  </form>

  <div class="text-center mt-3">
    <a class="geri" href="{{ url('/') }}"><i class="bi bi-arrow-left me-1"></i>Siteye dön</a>
  </div>
</div>

</body>
</html>

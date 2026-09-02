@extends('panel.layout')
@section('baslik', 'Kullanıcılar')

@section('icerik')

<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="mb-0" style="color:var(--metin-sonuk);font-size:.86rem">
    Temsilci yalnızca kendi kayıtlarını, yönetici tüm CRM'i, sahip ayrıca kullanıcıları yönetir.
  </p>
  <a href="{{ route('panel.kullanici.olustur') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> Yeni Kullanıcı
  </a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Ad Soyad</th><th>Kullanıcı Adı</th><th>Rol</th>
          <th>İletişim</th><th>Son Giriş</th><th>Durum</th><th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($kullanicilar as $k)
        <tr @class(['opacity-50' => !$k->durum])>
          <td class="fw-semibold">
            {{ $k->ad_soyad ?: '—' }}
            @if($k->id === auth()->id())
              <span class="badge bg-secondary ms-1" style="font-size:.62rem">siz</span>
            @endif
          </td>
          <td style="font-size:.85rem">{{ $k->kullanici }}</td>
          <td><span class="badge bg-secondary">{{ $roller[$k->rol] ?? $k->rol }}</span></td>
          <td style="font-size:.82rem">
            @if($k->eposta)<div>{{ $k->eposta }}</div>@endif
            @if($k->telefon)<div>{{ $k->telefon }}</div>@endif
            @unless($k->eposta || $k->telefon)<span style="color:var(--metin-sonuk)">—</span>@endunless
          </td>
          <td style="font-size:.82rem">{{ $k->son_giris?->format('d.m.Y H:i') ?? 'hiç girmedi' }}</td>
          <td>
            @if($k->durum)
              <span class="badge" style="background:#2f6d3a;color:#fff">Aktif</span>
            @else
              <span class="badge" style="background:#5c3a38;color:#fff">Pasif</span>
            @endif
          </td>
          <td class="text-end">
            <div class="d-flex gap-1 justify-content-end">
              <a href="{{ route('panel.kullanici.duzenle', $k) }}" class="btn btn-sm btn-outline-secondary" title="Düzenle">
                <i class="bi bi-pencil"></i>
              </a>
              @if($k->id !== auth()->id())
                <form method="post" action="{{ route('panel.kullanici.durum', $k) }}">
                  @csrf
                  <button class="btn btn-sm btn-outline-secondary"
                          title="{{ $k->durum ? 'Pasifleştir' : 'Aktifleştir' }}">
                    <i class="bi bi-{{ $k->durum ? 'pause' : 'play' }}"></i>
                  </button>
                </form>
              @endif
            </div>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>

<p class="mt-3" style="color:var(--metin-sonuk);font-size:.8rem">
  <i class="bi bi-info-circle me-1"></i>
  Ayrılan bir çalışanı silmek yerine <strong>pasifleştirin</strong> — geçmiş kayıtlardaki
  “sorumlu” bilgisi korunur.
</p>

@endsection

@extends('panel.layout')
@section('baslik', 'Faaliyet Alanları')

@section('icerik')

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <form method="get" class="d-flex gap-2">
    <input type="search" name="ara" value="{{ request('ara') }}" class="form-control form-control-sm"
           placeholder="Başlık veya sektör ailesi…" style="min-width:240px">
    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    @if(request('ara'))
      <a href="{{ route('panel.hizmet.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-x-lg"></i>
      </a>
    @endif
  </form>

  <a href="{{ route('panel.hizmet.olustur') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> Yeni Faaliyet Alanı
  </a>
</div>

@forelse($hizmetler as $grupAdi => $liste)
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>{{ $grupAdi }}</span>
      <span class="badge bg-secondary">{{ $liste->count() }}</span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr><th style="width:52px">Sıra</th><th>Başlık</th><th>Özet</th><th>Durum</th><th></th></tr>
        </thead>
        <tbody>
          @foreach($liste as $hizmet)
            <tr @class(['opacity-50' => !$hizmet->durum])>
              <td style="color:var(--metin-sonuk)">{{ $hizmet->sira }}</td>
              <td>
                <i class="bi {{ $hizmet->ikon }} me-2" style="color:var(--ap)"></i>
                <span class="fw-semibold">{{ $hizmet->baslik }}</span>
                @if($hizmet->baslik_en)
                  <div style="font-size:.76rem;color:var(--metin-sonuk)">{{ $hizmet->baslik_en }}</div>
                @endif
              </td>
              <td style="font-size:.83rem;color:var(--metin-soluk)">
                {{ \Illuminate\Support\Str::limit($hizmet->ozet, 70) ?: '—' }}
              </td>
              <td>
                @if($hizmet->durum)
                  <span class="badge" style="background:#2f6d3a;color:#fff">Yayında</span>
                @else
                  <span class="badge" style="background:#5c3a38;color:#fff">Gizli</span>
                @endif
              </td>
              <td class="text-end">
                <div class="d-flex gap-1 justify-content-end">
                  <a href="{{ route('site.faaliyet', $hizmet->slug) }}" target="_blank"
                     class="btn btn-sm btn-outline-secondary" title="Sitede gör">
                    <i class="bi bi-box-arrow-up-right"></i>
                  </a>
                  <a href="{{ route('panel.hizmet.duzenle', $hizmet) }}"
                     class="btn btn-sm btn-outline-secondary" title="Düzenle">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <form method="post" action="{{ route('panel.hizmet.durum', $hizmet) }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary"
                            title="{{ $hizmet->durum ? 'Yayından kaldır' : 'Yayına al' }}">
                      <i class="bi bi-{{ $hizmet->durum ? 'eye-slash' : 'eye' }}"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@empty
  <div class="card">
    <div class="card-body text-center" style="color:var(--metin-sonuk)">
      Kayıt bulunamadı.
    </div>
  </div>
@endforelse

@endsection

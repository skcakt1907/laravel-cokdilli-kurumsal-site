@extends('panel.layout')
@section('baslik', 'Haberler')

@section('icerik')

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <form method="get" class="d-flex gap-2">
    <input type="search" name="ara" value="{{ request('ara') }}" class="form-control form-control-sm"
           placeholder="Başlık veya kategori…" style="min-width:240px">
    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    @if(request('ara'))
      <a href="{{ route('panel.yazi.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
    @endif
  </form>
  <a href="{{ route('panel.yazi.olustur') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> Yeni Haber
  </a>
</div>

@if($yazilar->isEmpty())
  <div class="card">
    <div class="card-body text-center" style="color:var(--metin-sonuk)">
      Henüz haber yok. Haber eklediğinizde menüde “Haberler” bağlantısı da belirir.
    </div>
  </div>
@else
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr><th style="width:88px">Kapak</th><th>Başlık</th><th>Kategori</th>
              <th>Tarih</th><th>Durum</th><th></th></tr>
        </thead>
        <tbody>
        @foreach($yazilar as $yazi)
          <tr @class(['opacity-50' => !$yazi->durum])>
            <td>
              @if($yazi->gorsel)
                <img src="{{ \App\Support\Metin::gorselUrl($yazi->gorsel) }}" alt=""
                     style="height:38px;width:64px;object-fit:cover;border-radius:6px">
              @else
                <span style="color:var(--metin-sonuk)">—</span>
              @endif
            </td>
            <td>
              <span class="fw-semibold">{{ $yazi->baslik }}</span>
              @if($yazi->baslik_en)
                <div style="font-size:.76rem;color:var(--metin-sonuk)">{{ $yazi->baslik_en }}</div>
              @endif
            </td>
            <td style="font-size:.84rem">{{ $yazi->kategori ?: '—' }}</td>
            <td style="font-size:.83rem;white-space:nowrap">{{ $yazi->tarih?->format('d.m.Y') }}</td>
            <td>
              @if($yazi->durum)
                <span class="badge" style="background:#2f6d3a;color:#fff">Yayında</span>
              @else
                <span class="badge" style="background:#5c3a38;color:#fff">Taslak</span>
              @endif
            </td>
            <td class="text-end">
              <div class="d-flex gap-1 justify-content-end">
                @if($yazi->durum)
                  <a href="{{ route('site.haber', $yazi->slug) }}" target="_blank"
                     class="btn btn-sm btn-outline-secondary" title="Sitede gör">
                    <i class="bi bi-box-arrow-up-right"></i>
                  </a>
                @endif
                <a href="{{ route('panel.yazi.duzenle', $yazi) }}"
                   class="btn btn-sm btn-outline-secondary" title="Düzenle">
                  <i class="bi bi-pencil"></i>
                </a>
                <form method="post" action="{{ route('panel.yazi.durum', $yazi) }}">
                  @csrf
                  <button class="btn btn-sm btn-outline-secondary"
                          title="{{ $yazi->durum ? 'Taslağa al' : 'Yayına al' }}">
                    <i class="bi bi-{{ $yazi->durum ? 'eye-slash' : 'eye' }}"></i>
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
  <div class="mt-3">{{ $yazilar->links() }}</div>
@endif

@endsection

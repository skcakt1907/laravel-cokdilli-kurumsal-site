@extends('panel.layout')
@section('baslik', 'İş Ortakları')

@section('icerik')

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <form method="get" class="d-flex gap-2">
    <input type="search" name="ara" value="{{ request('ara') }}" class="form-control form-control-sm"
           placeholder="Ad, sektör veya ülke…" style="min-width:240px">
    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    @if(request('ara'))
      <a href="{{ route('panel.proje.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-x-lg"></i>
      </a>
    @endif
  </form>

  <a href="{{ route('panel.proje.olustur') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> Yeni Kayıt
  </a>
</div>

@foreach($turler as $turAnahtar => $turAdi)
  @php($liste = $kayitlar[$turAnahtar] ?? collect())

  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>{{ $turAdi }}</span>
      <span class="badge bg-secondary">{{ $liste->count() }}</span>
    </div>

    @if($liste->isEmpty())
      <div class="card-body text-center" style="color:var(--metin-sonuk)">
        Bu türde kayıt yok.
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr><th style="width:52px">Sıra</th><th style="width:88px">Logo</th>
                <th>Ad</th><th>Sektör</th><th>Ülke</th><th>Durum</th><th></th></tr>
          </thead>
          <tbody>
            @foreach($liste as $proje)
              <tr @class(['opacity-50' => !$proje->durum])>
                <td style="color:var(--metin-sonuk)">{{ $proje->sira }}</td>
                <td>
                  @if($proje->gorsel)
                    <div class="p-1" style="background:#fff;border-radius:6px;display:inline-block">
                      <img src="{{ \App\Support\Metin::gorselUrl($proje->gorsel) }}" alt=""
                           style="max-height:30px;max-width:64px">
                    </div>
                  @else
                    <span style="color:var(--metin-sonuk)">—</span>
                  @endif
                </td>
                <td>
                  <span class="fw-semibold">{{ $proje->baslik }}</span>
                  @if($proje->website)
                    <div style="font-size:.76rem;color:var(--metin-sonuk)">{{ $proje->website }}</div>
                  @endif
                </td>
                <td style="font-size:.84rem">{{ $proje->kategori ?: '—' }}</td>
                <td style="font-size:.84rem">{{ $proje->ulke ?: '—' }}</td>
                <td>
                  @if($proje->durum)
                    <span class="badge" style="background:#2f6d3a;color:#fff">Yayında</span>
                  @else
                    <span class="badge" style="background:#5c3a38;color:#fff">Gizli</span>
                  @endif
                </td>
                <td class="text-end">
                  <div class="d-flex gap-1 justify-content-end">
                    <a href="{{ route('site.ortak', $proje->slug) }}" target="_blank"
                       class="btn btn-sm btn-outline-secondary" title="Sitede gör">
                      <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    <a href="{{ route('panel.proje.duzenle', $proje) }}"
                       class="btn btn-sm btn-outline-secondary" title="Düzenle">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form method="post" action="{{ route('panel.proje.durum', $proje) }}">
                      @csrf
                      <button class="btn btn-sm btn-outline-secondary"
                              title="{{ $proje->durum ? 'Yayından kaldır' : 'Yayına al' }}">
                        <i class="bi bi-{{ $proje->durum ? 'eye-slash' : 'eye' }}"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
@endforeach

@endsection

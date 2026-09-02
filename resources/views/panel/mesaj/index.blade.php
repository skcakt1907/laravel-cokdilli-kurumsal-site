@extends('panel.layout')
@section('baslik', 'Mesajlar')

@section('icerik')

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <form method="get" class="d-flex flex-wrap gap-2 align-items-center">
    <input type="search" name="ara" value="{{ request('ara') }}" class="form-control form-control-sm"
           placeholder="Ad, e-posta, konu, içerik…" style="min-width:240px">

    <select name="durum" class="form-select form-select-sm" style="width:auto">
      <option value="">Tümü</option>
      <option value="okunmamis" @selected(request('durum') === 'okunmamis')>Okunmamış</option>
    </select>

    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>

    @if(request()->hasAny(['ara', 'durum']))
      <a href="{{ route('panel.mesaj.index') }}" class="btn btn-sm btn-outline-secondary" title="Temizle">
        <i class="bi bi-x-lg"></i>
      </a>
    @endif
  </form>

  @if($okunmamis > 0)
    <span class="badge bg-secondary">{{ $okunmamis }} okunmamış</span>
  @endif
</div>

@if($mesajlar->isEmpty())
  <div class="card">
    <div class="card-body text-center" style="color:var(--metin-sonuk)">
      @if(request()->hasAny(['ara', 'durum']))
        Bu filtreye uyan mesaj yok.
      @else
        Henüz mesaj yok. Sitedeki iletişim formundan gelenler burada listelenir.
      @endif
    </div>
  </div>
@else
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr><th style="width:34px"></th><th>Gönderen</th><th>Konu</th><th>Tarih</th><th></th></tr>
        </thead>
        <tbody>
          @foreach($mesajlar as $mesaj)
            <tr @class(['fw-semibold' => !$mesaj->okundu])>
              <td class="text-center">
                <i class="bi bi-{{ $mesaj->okundu ? 'envelope-open' : 'envelope-fill' }}"
                   style="color:{{ $mesaj->okundu ? 'var(--metin-sonuk)' : 'var(--ap)' }}"
                   title="{{ $mesaj->okundu ? 'Okundu' : 'Okunmadı' }}"></i>
              </td>
              <td>
                <a href="{{ route('panel.mesaj.detay', $mesaj) }}">{{ $mesaj->ad }}</a>
                <div style="font-size:.78rem;color:var(--metin-sonuk)">{{ $mesaj->mail }}</div>
              </td>
              <td style="font-size:.88rem">
                {{ $mesaj->konu ?: '—' }}
                <div style="font-size:.78rem;color:var(--metin-sonuk)">
                  {{ \Illuminate\Support\Str::limit($mesaj->mesaj, 70) }}
                </div>
              </td>
              <td style="font-size:.82rem;white-space:nowrap">{{ $mesaj->tarih?->format('d.m.Y H:i') }}</td>
              <td class="text-end">
                <a href="{{ route('panel.mesaj.detay', $mesaj) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-eye"></i>
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">{{ $mesajlar->links() }}</div>
@endif

@endsection

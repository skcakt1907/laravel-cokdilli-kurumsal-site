@extends('panel.layout')
@section('baslik', 'Fırsat Panosu')

@push('stil')
<style>
  .pano{ display:flex; gap:1rem; overflow-x:auto; padding-bottom:1rem; align-items:flex-start; }
  .sutun{ flex:0 0 290px; background:var(--yuzey); border:1px solid var(--cizgi);
          border-radius:12px; display:flex; flex-direction:column; max-height:calc(100vh - 220px); }
  .sutun-bas{ padding:.85rem 1rem; border-bottom:1px solid var(--cizgi); position:sticky; top:0;
              background:var(--yuzey); border-radius:12px 12px 0 0; }
  .sutun-bas .ad{ font-weight:600; font-size:.9rem; color:var(--metin); display:flex;
                  align-items:center; gap:.5rem; }
  .sutun-bas .nokta{ width:9px; height:9px; border-radius:50%; flex-shrink:0; }
  .sutun-bas .ozet{ font-size:.75rem; color:var(--metin-sonuk); margin-top:.25rem; }
  .sutun-govde{ padding:.7rem; overflow-y:auto; flex:1; min-height:80px; }
  .sutun.uzerinde{ outline:2px dashed var(--ap); outline-offset:-4px; }

  .kart{ background:var(--yuzey-2); border:1px solid var(--cizgi); border-radius:9px;
         padding:.75rem .8rem; margin-bottom:.6rem; cursor:grab; }
  .kart:active{ cursor:grabbing; }
  .kart.suruklenen{ opacity:.45; }
  .kart .baslik{ font-size:.87rem; font-weight:600; color:var(--metin); }
  .kart .musteri{ font-size:.77rem; color:var(--metin-sonuk); margin-top:.15rem; }
  .kart .alt{ display:flex; justify-content:space-between; align-items:center; margin-top:.55rem; }
  .kart .tutar{ font-size:.82rem; font-weight:700; color:var(--ap); }
  .kart .kisi{ font-size:.7rem; color:var(--metin-sonuk); }
  .bos{ text-align:center; color:var(--metin-sonuk); font-size:.8rem; padding:1.2rem .5rem; }
</style>
@endpush

@section('icerik')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  @if($hatlar->count() > 1)
    <form method="get">
      <select name="hat" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
        @foreach($hatlar as $h)
          <option value="{{ $h->id }}" @selected($h->id === $hat->id)>{{ $h->adi }}</option>
        @endforeach
      </select>
    </form>
  @else
    <div style="color:var(--metin-sonuk);font-size:.86rem">{{ $hat->adi }}</div>
  @endif

  <a href="{{ route('panel.firsat.olustur') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> Yeni Fırsat
  </a>
</div>

<div class="pano" id="pano">
  @foreach($asamalar as $asama)
    @php($liste = $firsatlar[$asama->id] ?? collect())
    @php($toplam = $liste->groupBy('para_birimi')->map(fn($g) => $g->sum('tutar')))

    <div class="sutun" data-asama="{{ $asama->id }}">
      <div class="sutun-bas">
        <div class="ad">
          <span class="nokta" style="background:{{ $asama->renk ?: 'var(--metin-sonuk)' }}"></span>
          {{ $asama->adi }}
          <span class="ms-auto badge bg-secondary">{{ $liste->count() }}</span>
        </div>
        <div class="ozet">
          @if($toplam->isEmpty())
            —
          @else
            @foreach($toplam as $birim => $tut)
              {{ number_format((float) $tut, 0, ',', '.') }} {{ $birim }}@if(!$loop->last) · @endif
            @endforeach
          @endif
          @if($asama->sonuc === 'acik') · %{{ $asama->olasilik }} @endif
        </div>
      </div>

      <div class="sutun-govde" data-birak="{{ $asama->id }}">
        @forelse($liste as $firsat)
          <div class="kart" draggable="true" data-firsat="{{ $firsat->id }}">
            <a href="{{ route('panel.firsat.duzenle', $firsat) }}" class="baslik d-block text-decoration-none">
              {{ $firsat->baslik }}
            </a>
            <div class="musteri">
              <i class="bi bi-building me-1"></i>{{ $firsat->musteri?->adi ?? '—' }}
            </div>
            <div class="alt">
              <span class="tutar">
                {{ number_format((float) $firsat->tutar, 0, ',', '.') }} {{ $firsat->para_birimi }}
              </span>
              <span class="kisi">{{ $firsat->sorumlu?->ad_soyad ?? 'Atanmamış' }}</span>
            </div>
          </div>
        @empty
          <div class="bos">Bu aşamada fırsat yok</div>
        @endforelse
      </div>
    </div>
  @endforeach
</div>

@endsection

@push('betik')
<script>
// Sürükle-bırak: kart bir sütuna bırakıldığında sunucuya taşıma isteği gider.
// Sunucu reddederse (yetki yok vb.) kart eski yerine döner.
(() => {
  const pano  = document.getElementById('pano');
  const token = document.querySelector('meta[name="csrf-token"]')?.content;
  let suruklenen = null, eskiKap = null;

  pano.addEventListener('dragstart', e => {
    const kart = e.target.closest('.kart');
    if (!kart) return;
    suruklenen = kart;
    eskiKap = kart.parentElement;
    kart.classList.add('suruklenen');
    e.dataTransfer.effectAllowed = 'move';
  });

  pano.addEventListener('dragend', () => {
    suruklenen?.classList.remove('suruklenen');
    document.querySelectorAll('.sutun.uzerinde').forEach(s => s.classList.remove('uzerinde'));
    suruklenen = null;
  });

  pano.addEventListener('dragover', e => {
    const kap = e.target.closest('[data-birak]');
    if (!kap || !suruklenen) return;
    e.preventDefault();
    kap.closest('.sutun').classList.add('uzerinde');
  });

  pano.addEventListener('dragleave', e => {
    const sutun = e.target.closest('.sutun');
    if (sutun && !sutun.contains(e.relatedTarget)) sutun.classList.remove('uzerinde');
  });

  pano.addEventListener('drop', async e => {
    const kap = e.target.closest('[data-birak]');
    if (!kap || !suruklenen) return;
    e.preventDefault();
    kap.closest('.sutun').classList.remove('uzerinde');

    const kart     = suruklenen;
    const oncekiK  = eskiKap;
    const asamaId  = kap.dataset.birak;
    const firsatId = kart.dataset.firsat;

    if (oncekiK === kap) return;

    kap.appendChild(kart);           // önce göster, sonra doğrula

    try {
      const y = await fetch(`{{ url('yonetim/firsatlar') }}/${firsatId}/tasi`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: JSON.stringify({ stage_id: asamaId }),
      });
      if (!y.ok) throw new Error(y.status);
      location.reload();             // sütun toplamları güncellensin
    } catch (hata) {
      oncekiK.appendChild(kart);     // sunucu reddetti, geri al
      alert('Fırsat taşınamadı. Yetkiniz olmayabilir veya oturumunuz düşmüş olabilir.');
    }
  });
})();
</script>
@endpush

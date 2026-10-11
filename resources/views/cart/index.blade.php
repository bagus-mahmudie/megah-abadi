@extends('layouts.app')

@section('content')
<section class="py-[60px]">
  <div class="container grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">

    {{-- ========================= LIST ITEM ========================= --}}
    <div class="space-y-0">
      @forelse ($items as $it)
        @php
          $p       = $it->product;
          $cat     = optional($p->category)->name ?: 'Lorem ipsum';
          $rating  = number_format($p->average_rating ?? 0, 1);
          $imgRel  = optional($p->images->first())->image_url;
          $img     = $imgRel && file_exists(public_path(ltrim($imgRel,'/')))
                    ? asset(ltrim($imgRel,'/'))
                    : asset('assets/img/placeholder-product.png');

          $hasDisc = ($p->discount_percent ?? 0) > 0;
          $discPct = (float)($p->discount_percent ?? 0);
          $unit    = (float)($it->price_snapshot ?? 0);    // harga satuan (TIDAK dikali qty)
        @endphp

        <article class="grid grid-cols-[48px_1fr] gap-2 rounded-2xl bg-white p-3">
            {{-- Kolom ikon kiri: checkbox + hapus (rata tengah) --}}
            <div class="h-full flex flex-col items-center justify-center gap-3">
            {{-- Checkbox select --}}
            <input
                id="sel-{{ $it->id }}"
                type="checkbox"
                name="selected[]"
                value="{{ $it->id }}"
                class="peer sr-only"
            >
            <label for="sel-{{ $it->id }}"
                    class="h-8 w-8 inline-flex items-center justify-center rounded-md border
                            text-slate-500 hover:bg-slate-50 cursor-pointer
                            peer-checked:bg-brand-green peer-checked:text-white peer-checked:border-brand-green">
                <i class="bi bi-check-lg"></i>
            </label>

            {{-- Tombol hapus --}}
            <button type="button"
                    class="h-8 w-8 inline-flex items-center justify-center rounded-md border text-slate-500 hover:bg-slate-50"
                    data-cart-remove
                    data-item-id="{{ $it->id }}"
                    aria-label="Hapus">
                <i class="bi bi-trash"></i>
            </button>
            </div>

            {{-- Kartu utama --}}
            <div class="rounded-xl shadow-md border-slate-100 p-3 sm:p-4">
                <div class="grid grid-cols-[0.7fr_1.3fr] gap-4">
                {{-- Gambar + badge + wishlist (klik gambar menuju detail, full height) --}}
                <div class="relative h-full">
                <a href="{{ route('products.show', ['product' => $p->id, 'from' => 'cart']) }}"
                    class="block h-full">
                    <img src="{{ $img }}" 
                        alt="{{ $p->name }}"
                        class="w-full h-full object-cover rounded-xl">
                </a>

                @if($hasDisc ?? false)
                    <span class="absolute left-2 top-2 rounded-md bg-brand-green px-2 py-1 text-[14px] text-white">
                    {{ rtrim(rtrim(number_format($discPct ?? 0, 2, '.', ''), '0'), '.') }}% off
                    </span>
                @endif

                <button class="absolute right-2 top-2 inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/95 shadow">
                    <i class="bi bi-heart"></i>
                </button>
                </div>

                {{-- Info + bottom row: HARGA ⟷ QTY sejajar --}}
                <div class="min-w-0">
                    <div class="flex items-center justify-between">
                    <span class="text-brand-green text-[16px]">{{ $cat }}</span>
                    <span class="inline-flex items-center gap-1 text-yellow-400 text-[16px]">
                        <i class="bi bi-star-fill"></i>
                        <span class="text-slate-700 font-medium">{{ $rating }}</span>
                    </span>
                    </div>

                    <h3 class="mt-1 text-[15px] sm:text-[18px] font-[500] leading-snug line-clamp-2 min-h-[3rem]">
                    {{ $p->name }}
                    </h3>

                    <p class="mt-1 text-[16px] text-slate-500 line-clamp-2">
                    {{ strip_tags($p->description_html ?? '') }}
                    </p>

                    {{-- BARIS BAWAH: harga kiri, qty kanan --}}
                    <div class="mt-3 flex items-center justify-between">
                    <div class="text-[16px] font-[600]">
                        Rp {{ number_format($unit, 0, ',', '.') }}
                    </div>

                    <div class="inline-flex items-center gap-2 rounded-[12px] bg-slate-100 px-2 py-1">
                        <button class="h-8 w-8 inline-flex items-center justify-center rounded-md text-slate-700 hover:bg-white"
                                data-cart-minus data-item-id="{{ $it->id }}">−</button>
                        <span id="qty-{{ $it->id }}" class="min-w-[1.5rem] text-center font-medium text-slate-800">
                        {{ $it->qty }}
                        </span>
                        <button class="h-8 w-8 inline-flex items-center justify-center rounded-md text-slate-700 hover:bg-white"
                                data-cart-plus data-item-id="{{ $it->id }}">+</button>
                    </div>
                    </div>
                </div>
                </div>
            </div>
        </article>

      @empty
        <div class="rounded-2xl bg-white p-8 text-center text-slate-600 shadow-sm">
          Keranjang masih kosong.
        </div>
      @endforelse
    </div>

    {{-- ========================= RINGKASAN ========================= --}}
    <aside class="rounded-2xl bg-white p-5 shadow-md h-fit">
      <h3 class="text-[20px] font-semibold mb-3">Ringkasan Pembayaran</h3>
      <hr class="my-2">
      <dl class="space-y-3 text-[18px]">
        <div class="flex justify-between">
          <dt>Subtotal</dt><dd id="sum-subtotal">Rp {{ number_format($subtotal,0,',','.') }}</dd>
        </div>
        <div class="flex justify-between">
          <dt>Ongkir (Reguler)</dt><dd id="sum-shipping">Rp {{ number_format($shipping,0,',','.') }}</dd>
        </div>
        <div class="flex justify-between">
          <dt>Diskon Promo</dt><dd id="sum-discount">- Rp {{ number_format($discount,0,',','.') }}</dd>
        </div>
        <hr class="my-2">
        <div class="flex justify-between font-semibold text-[18px]">
          <dt>Total Bayar</dt><dd id="sum-total">Rp {{ number_format($total,0,',','.') }}</dd>
        </div>
      </dl>
      <a href="{{ route('checkout.show') }}"
         class="mt-4 block rounded-xl bg-brand-green py-3 text-center text-[18px] font-medium text-white hover:bg-brand-greenDark">
        Checkout
      </a>
    </aside>

  </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
  const token    = document.querySelector('meta[name="csrf-token"]')?.content;
  const badge    = document.getElementById('navCartBadge');
  const HOME_URL = "{{ route('home') }}";

  // === Redirect ke Home bila ada request AJAX yang mengembalikan 401/419 ===
  const _fetch = window.fetch;
  window.fetch = async function(input, init) {
    const res = await _fetch(input, init);
    if (res.status === 401 || res.status === 419) {
      // user sudah logout / sesi kadaluarsa -> kembali ke Home
      window.location.replace(HOME_URL);
      // kembalikan res agar pemanggil yang tidak kena redirect tidak error
      return res;
    }
    return res;
  };

  const rupiah = n => new Intl.NumberFormat('id-ID').format(n || 0);

  async function patchQty(id, qty) {
    const res = await fetch(`{{ url('/cart/item') }}/${id}/qty`, {
      method: 'PATCH',
      headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json'},
      body: new URLSearchParams({ qty: String(qty) })
    });
    if (!res.ok) throw new Error('Qty gagal update');
    return res.json();
  }

  async function removeItem(id) {
    const res = await fetch(`{{ url('/cart/item') }}/${id}`, {
      method: 'DELETE',
      headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json'}
    });
    if (!res.ok) throw new Error('Gagal hapus item');
    return res.json();
  }

  function applySummary(d){
    if (typeof d.subtotal !== 'undefined')
      document.getElementById('sum-subtotal').textContent = 'Rp ' + rupiah(d.subtotal);
    if (typeof d.total !== 'undefined')
      document.getElementById('sum-total').textContent = 'Rp ' + rupiah(d.total);
    if (badge && typeof d.cart_count !== 'undefined')
      badge.textContent = d.cart_count > 99 ? '99+' : d.cart_count;
  }

  // + qty
  document.addEventListener('click', async (e)=>{
    const btn = e.target.closest('[data-cart-plus]');
    if (!btn) return;
    const id  = btn.getAttribute('data-item-id');
    const qtyEl = document.getElementById('qty-'+id);
    const next = (parseInt(qtyEl.textContent||'1',10) || 1) + 1;
    try {
      const d = await patchQty(id, next);
      qtyEl.textContent = next;
      applySummary(d);
    } catch { alert('Gagal update qty'); }
  });

  // − qty
  document.addEventListener('click', async (e)=>{
    const btn = e.target.closest('[data-cart-minus]');
    if (!btn) return;
    const id  = btn.getAttribute('data-item-id');
    const qtyEl = document.getElementById('qty-'+id);
    const curr = parseInt(qtyEl.textContent||'1',10) || 1;
    const next = Math.max(1, curr-1);
    if (next === curr) return;
    try {
      const d = await patchQty(id, next);
      qtyEl.textContent = next;
      applySummary(d);
    } catch { alert('Gagal update qty'); }
  });

  // hapus
  document.addEventListener('click', async (e)=>{
    const btn = e.target.closest('[data-cart-remove]');
    if (!btn) return;
    const id  = btn.getAttribute('data-item-id');
    if (!confirm('Hapus item ini dari keranjang?')) return;
    try {
      const d = await removeItem(id);
      // hapus DOM kartu (article terdekat)
      const card = btn.closest('article');
      card?.parentNode?.removeChild(card);
      applySummary(d);
    } catch { alert('Gagal hapus item'); }
  });
})();
</script>
@endpush


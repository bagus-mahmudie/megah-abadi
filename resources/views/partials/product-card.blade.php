@php
  /** @var \Illuminate\Database\Eloquent\Model|array $p */
  $isArray     = is_array($p);

  // Field untuk UI (aman utk model/array)
  $title       = $isArray ? data_get($p, 'name') : ($p->name ?? data_get($p, 'title'));
  $category    = $isArray ? data_get($p, 'category.name') : optional($p->category)->name;
  $rating      = (float) ($isArray ? data_get($p, 'average_rating', 0) : ($p->average_rating ?? 0));
  $basePrice   = (float) ($isArray ? data_get($p, 'base_price', 0) : ($p->base_price ?? 0));
  $discountPct = $isArray ? data_get($p, 'discount_percent') : ($p->discount_percent ?? null);
  $hasDiscount = $discountPct && $discountPct > 0;
  $finalPrice  = $hasDiscount ? round($basePrice * (1 - $discountPct/100)) : $basePrice;

  // Gambar
  $images         = $isArray ? (data_get($p, 'images', []) ?: []) : ($p->relationLoaded('images') ? $p->images : collect());
  $firstImageUrl  = $isArray ? data_get($images, '0.image_url') : optional($images->first())->image_url;
  $candidate      = $firstImageUrl ? ltrim($firstImageUrl, '/') : null;
  $candidateFs    = $candidate ? public_path($candidate) : null;
  $img            = ($candidate && file_exists($candidateFs)) ? asset($candidate) : asset('assets/img/placeholder-product.png');

  // Label/URL
  $tagLabel  = $category ?: 'Lorem ipsum';
  $id        = $isArray ? data_get($p, 'id') : $p->id;
  $fromParam = isset($from) && $from !== '' ? (string) $from : null;
  $params    = ['product' => $id];
  if ($fromParam) $params['from'] = $fromParam;
  $showUrl   = route('products.show', $params);
@endphp

<article class="relative rounded-[16px] mx-2 bg-white shadow-[0_0_20px_rgba(0,0,0,0.04)] overflow-hidden"
         data-product-card>
  {{-- wishlist --}}
  <button class="absolute right-5 top-5 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-slate-700 shadow-soft">
    <i class="bi bi-heart"></i>
  </button>

  {{-- badge diskon --}}
  @if($hasDiscount)
    <div class="absolute left-5 top-5 z-10 rounded-md bg-brand-green px-2 py-1 text-xs font-semibold text-white">
      {{ rtrim(rtrim(number_format($discountPct, 2, '.', ''), '0'), '.') }}% off
    </div>
  @endif

  {{-- gambar --}}
  <a href="{{ $showUrl }}" class="relative p-3 pb-0 block">
    <img src="{{ $img }}" alt="{{ $title }}" class="h-full w-full rounded-[14px] object-cover">
  </a>

  {{-- body --}}
  <div class="p-4">
    <div class="flex items-center justify-between">
      <span class="text-brand-green text-[16px]">{{ $tagLabel }}</span>
      <span class="inline-flex items-center gap-1 text-yellow-400 text-sm">
        <i class="bi bi-star-fill"></i>
        <span class="text-slate-700 text-[16px] font-medium">{{ number_format($rating, 1) }}</span>
      </span>
    </div>

    <h3 class="mt-2 text-[20px] font-[500] leading-snug text-slate-900 line-clamp-2 min-h-[3.5rem]">
      {!! nl2br(e($title)) !!}
    </h3>

    {{-- qty --}}
   

    <div class="mt-2 flex items-center justify-between">
      <div class="text-slate-500">Kuantitas</div>
      <div class="qty-control flex items-center gap-1 rounded-[12px] bg-slate-100 px-1 py-1 select-none">
        <button type="button"
                data-qty-dec
                class="h-7 w-7 grid items-center justify-center rounded-md text-slate-700 hover:bg-slate-50">−</button>
        <span data-qty-val
              class="min-w-[2.5rem] text-center grid place-items-center border-l border-r font-medium text-slate-800">1</span>
        <button type="button"
                data-qty-inc
                class="h-7 w-7 grid items-center justify-center rounded-md text-slate-700 hover:bg-slate-50">+</button>
      </div>
    </div>

    <div class="mt-3 flex items-center justify-between">
      <div class="text-[18px] font-[500] text-slate-900">
        Rp {{ number_format($finalPrice, 0, ',', '.') }}
      </div>

      @auth
        <button type="button"
                class="px-[24px] inline-flex items-center justify-center gap-2 rounded-[12px] bg-brand-green/10 py-1 font-medium text-brand-green hover:bg-brand-green/15"
                data-add-to-cart
                data-product-id="{{ $id }}"
                aria-label="Tambah ke keranjang">
          <i class="bi bi-handbag text-[20px]"></i>
        </button>
      @else
        <button type="button" data-auth-open="login"
                class="px-[24px] inline-flex items-center justify-center gap-2 rounded-[12px] bg-brand-green/10 py-1 font-medium text-brand-green hover:bg-brand-green/15">
          <i class="bi bi-handbag text-[20px]"></i>
        </button>
      @endauth
    </div>
  </div>
</article>

@push('scripts')
<script>
(function () {
  // ================= Qty controls (hindari double binding)
  if (!window.__qtyBooted) {
    window.__qtyBooted = true;

    document.addEventListener('click', (e) => {
      const inc = e.target.closest('[data-qty-inc]');
      if (inc) {
        const wrap = inc.closest('.qty-control');
        const valEl = wrap?.querySelector('[data-qty-val]');
        const cur = parseInt(valEl?.textContent || '1', 10) || 1;
        valEl.textContent = String(cur + 1);
        return;
      }
      const dec = e.target.closest('[data-qty-dec]');
      if (dec) {
        const wrap = dec.closest('.qty-control');
        const valEl = wrap?.querySelector('[data-qty-val]');
        const cur = parseInt(valEl?.textContent || '1', 10) || 1;
        valEl.textContent = String(Math.max(1, cur - 1));
        return;
      }
    });
  }

  // ================= Add to Cart (hindari double binding)
  if (window.__addCartBooted) return;
  window.__addCartBooted = true;

  const token = document.querySelector('meta[name="csrf-token"]')?.content;
  const badge = document.getElementById('navCartBadge');

  async function addToCart(productId, qty) {
    const res = await fetch(`{{ url('/cart/add') }}/${productId}`, {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json'},
      body: new URLSearchParams({ qty: String(qty) }),
      credentials: 'same-origin'
    });

    // jika sesi expired dan server balas 401
    if (res.status === 401) {
      // buka modal login (sesuaikan trigger yang kamu punya)
      window.SHOW_AUTH_MODAL = 'login';
      document.querySelector('[data-auth-open="login"]')?.click();
      return { ok: false };
    }

    if (!res.ok) throw new Error('Request gagal');
    return res.json();
  }

    document.addEventListener('click', async (e) => {
    // hanya respon jika tombolnya berada DI DALAM elemen kartu
    const btn = e.target.closest('[data-product-card] [data-add-to-cart]');
    if (!btn) return;

    const card = btn.closest('[data-product-card]');
    const qtyEl = card?.querySelector('[data-qty-val]');
    const qty = Math.max(1, parseInt(qtyEl?.textContent || '1', 10) || 1);
    const pid = btn.getAttribute('data-product-id');

    try {
      const data = await addToCart(pid, qty);
      if (!data || !data.ok) return;

      // Update badge = jumlah item unik
      if (badge && typeof data.cart_count !== 'undefined') {
        badge.textContent = data.cart_count;
        // animasi kecil
        badge.style.transform = 'scale(1.15)';
        setTimeout(() => { badge.style.transform = ''; }, 140);
      }
    } catch (err) {
      console.error(err);
      alert('Gagal menambahkan ke keranjang. Coba lagi.');
    }
  });
})();
</script>
@endpush

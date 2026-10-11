@php
  $showProducts = $products instanceof \Illuminate\Pagination\LengthAwarePaginator
      ? collect($products->items())->take(9)
      : collect($products)->take(9);
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-x-1 gap-y-4">
  @forelse ($showProducts as $p)
    @include('partials.product-card', ['p' => $p])
  @empty
    <div class="col-span-3 rounded-xl bg-white p-6 text-center text-slate-500 shadow-soft">
      Tidak ada produk yang cocok.
    </div>
  @endforelse
</div>

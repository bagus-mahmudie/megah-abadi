@extends('layouts.app')

@section('content')
@php
  // ===== Data Produk & Fallback Gambar =====
  $images = $product->images ?? collect();
  $placeholder = asset('assets/img/placeholder-product.png');

  // Fungsi aman untuk ambil gambar pertama yang valid
  $getImage = function($imgPath) use ($placeholder) {
      if (!$imgPath) return $placeholder;
      $clean = ltrim($imgPath, '/');
      $fullPath = public_path($clean);
      return file_exists($fullPath) ? asset($clean) : $placeholder;
  };

  // Gambar utama
  $mainImg = $images->first()?->image_url
      ? $getImage($images->first()->image_url)
      : $placeholder;

  // Semua thumbnail (fallback juga)
  $thumbs = $images->count()
      ? $images->map(fn($img) => $getImage($img->image_url))
      : collect([$placeholder, $placeholder, $placeholder]);

  $rating = number_format($product->average_rating ?? 4.9, 1);
  $base   = (float)($product->base_price ?? 0);
  $disc   = (float)($product->discount_percent ?? 0);
  $hasDiscount = $disc > 0;
  $finalPrice  = $hasDiscount ? round($base * (1 - $disc/100)) : $base;
@endphp

<section class="py-[60px] bg-white">
  <div class="container mx-auto px-4">

    {{-- ============================ TOP: SAMA TINGGI ============================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

      {{-- ===== LEFT: KARTU GALERI ===== --}}
      <div class="lg:col-span-6 h-full">
        <div x-data="{ main:'{{ $mainImg }}' }"
             class="h-full flex flex-col rounded-[16px] bg-white shadow-md p-4">
          {{-- gambar utama --}}
          <img :src="main" alt="{{ $product->name }}"
               class="w-full h-[520px] rounded-[12px] object-cover">

          {{-- thumbnails: 3 buah sejajar --}}
          <div class="mt-4 grid grid-cols-3 gap-4">
            @foreach(collect($thumbs)->take(3) as $t)
              <button
                @click="main='{{ $t }}'"
                class="overflow-hidden rounded-[12px] ring-1 ring-slate-200 hover:ring-brand-green transition">
                <img src="{{ $t }}" alt="thumb"
                     class="w-full h-full object-cover rounded-[12px]">
              </button>
            @endforeach
          </div>
        </div>
      </div>

      {{-- ===== RIGHT: KARTU DETAIL ===== --}}
      <div class="lg:col-span-6 h-full">
        <div class="h-full flex flex-col relative rounded-[16px] bg-white shadow-md p-6">

          {{-- ikon favorit kanan-atas --}}
          <button class="absolute right-4 top-4 inline-flex h-9 w-9 items-center justify-center
                         text-[24px] text-slate-700 ">
            <i class="bi bi-box-arrow-up"></i>
          </button>

          {{-- kategori kecil --}}
          <p class="text-[20px] text-brand-green mb-1">
            {{ optional($product->category)->name ?? 'Category' }}
          </p>

          {{-- judul --}}
          <h1 class="text-[28px] md:text-[28px] font-semibold text-slate-900 leading-snug mb-4">
            {{ $product->name }}
          </h1>

          {{-- rating + favorit count --}}
          <div class=" flex items-center gap-3 ">
            <span class="flex items-center text-yellow-400 text-[18px]">
              <i class="bi bi-star-fill mr-1"></i>{{ $rating }}
            </span>
            <span class="text-slate-400">•</span>
            <span class="text-slate-600 text-[20px] items-center"><i class="bi bi-heart"></i> Favorit ({{ number_format($product->favorite_count ?? 5400) }})</span>
          </div>

          {{-- harga + diskon seperti mockup (harga coret + `30% off`) --}}
          <div class="mt-4 flex items-center gap-3 border-t border-b py-4">
            <div class="text-[22px] md:text-[24px] font-semibold text-slate-900">
              Rp {{ number_format($finalPrice, 0, ',', '.') }}
            </div>
            @if($hasDiscount)
              <div class="text-slate-400 line-through text-[16px]">
                Rp {{ number_format($base, 0, ',', '.') }}
              </div>
              <span class="text-slate-600 text-[16px]">
                {{ rtrim(rtrim(number_format($product->discount_percent, 2, '.', ''), '0'), '.') }}% off
              </span>
            @endif
          </div>

          {{-- pilihan warna = chip --}}
          <div class="mt-6">
            <div class="text-slate-700 text-[20px] mb-2">Warna</div>
            <div class="flex flex-wrap gap-3 mb-4">
              @foreach(['Coklat','Hijau','Hijau Muda','Abu Abu','Orange Tua'] as $color)
                <button
                  class="inline-flex items-center justify-center rounded-[12px] bg-slate-100
                         px-5 py-2 text-[18px] text-slate-700
                         hover:bg-brand-green hover:text-white transition">
                  {{ $color }}
                </button>
              @endforeach
            </div>
          </div>

          {{-- KUANTITAS + TOMBOL (versi stack + full width) --}}
          <div x-data="{ qty: 1 }" class="mt-6">

            <div class="text-slate-700 text-[18px] mb-3">Kuantitas</div>

            {{-- kontrol qty --}}
            <div class="inline-flex overflow-hidden rounded-[12px] text-[16px] shadow-sm bg-slate-100">
              <button type="button"
                      @click="qty = Math.max(1, qty - 1)"
                      class="h-10 w-12 grid place-items-center text-slate-700 hover:bg-slate-50">−</button>
              <span id="detailQty"
                    class="h-10 min-w-[64px] grid place-items-center border-l border-r font-medium text-slate-800"
                    x-text="qty"></span>
              <button type="button"
                      @click="qty++"
                      class="h-10 w-12 grid place-items-center text-slate-700 hover:bg-slate-50">+</button>
            </div>

            <div class="my-6 h-px bg-slate-200"></div>

            {{-- BELI SEKARANG (primary) --}}
            <button type="button"
                    class="mb-3 w-full rounded-[12px] bg-brand-green py-3 text-center text-[18px] text-white hover:bg-brand-greenDark">
              Beli Sekarang <i class="bi bi-arrow-right text-[20px] ms-2"></i>
            </button>

            {{-- MASUKKAN KERANJANG (secondary) --}}
            @auth
              <button type="button"
                      class="w-full rounded-[12px] bg-white py-3 text-center text-[18px]  text-slate-800 ring-1 ring-slate-200 hover:bg-slate-50 inline-flex items-center justify-center gap-2"
                      data-add-to-cart
                      data-product-id="{{ $product->id }}"
                      data-qty-el="#detailQty">
                <i class="bi bi-handbag text-[20px]"></i>
                Masukkan keranjang
              </button>
            @else
              <button type="button"
                      class="w-full rounded-[12px] bg-white py-3 text-center text-[18px] font-medium text-slate-800 ring-1 ring-slate-200 hover:bg-slate-50 inline-flex items-center justify-center gap-2"
                      data-auth-open="login">
                <i class="bi bi-handbag text-[20px]"></i>
                Masukkan keranjang
              </button>
            @endauth
          </div>


          {{-- tambahkan flex-grow agar isi memenuhi tinggi --}}
          <div class="flex-grow"></div>

        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-[1.2fr_0.8fr] gap-6 items-stretch mt-4">

      {{-- KIRI: kolom kiri (chat + deskripsi) --}}
      <div class="md:col-span-1 flex flex-col gap-4">
        
        {{-- Card Chat Sekarang --}}
        <a href="{{ $product->chat_url ?? '#' }}"
          class="flex items-center justify-between w-full bg-white rounded-2xl shadow-sm px-5 py-3 hover:bg-slate-50 transition">
          <div class="flex items-center gap-2 text-slate-700 text-[18px]">
            <i class="bi bi-chat-dots "></i>
            <span class="font-medium">Chat Sekarang</span>
          </div>
          <i class="bi bi-arrow-right text-slate-500"></i>
        </a>

        {{-- Card Deskripsi / Spesifikasi --}}
        <div x-data="{ tab: 'desc' }"
            class="bg-white shadow-md rounded-2xl h-full flex flex-col">
          {{-- Tabs --}}
          <div class="px-5 pt-5 border-b border-slate-200 flex items-center gap-6">
            <button
              class="pb-3 -mb-px border-b-2"
              :class="tab==='desc' ? 'border-brand-green text-slate-900 font-medium text-[18px]' : 'border-transparent text-slate-500 text-[18px]'"
              @click="tab='desc'">Deskripsi Produk</button>

            <button
              class="pb-3 -mb-px border-b-2"
              :class="tab==='spec' ? 'border-brand-green text-slate-900 font-medium text-[18px]' : 'border-transparent text-slate-500 text-[18px]'"
              @click="tab='spec'">Spesifikasi Produk</button>
          </div>

          {{-- Konten --}}
          <div class="p-5 text-slate-700 leading-relaxed grow">
            <div x-show="tab==='desc'">
              {!! $product->description_html !!}
            </div>
            <div x-show="tab==='spec'">
              {!! $product->spec_html !!}
            </div>
          </div>
          
        </div>
      </div>


      {{-- KANAN: Penilaian Produk --}}
      @php
        use Illuminate\Support\Str;

        $rawTotal = (int)($summary->total_reviews ?? 0);
        $denom    = max(1, $rawTotal); // hindari pembagi nol
        $avg      = number_format((float)($summary->avg_rating ?? 0), 1);
        $dist     = [
          5 => (int)($summary->count_5 ?? 0),
          4 => (int)($summary->count_4 ?? 0),
          3 => (int)($summary->count_3 ?? 0),
          2 => (int)($summary->count_2 ?? 0),
          1 => (int)($summary->count_1 ?? 0),
        ];
        $pct = fn($n) => $denom ? round($n / $denom * 100) : 0;
      @endphp

      {{-- ===== KANAN: Penilaian Produk + Tombol Tambah Review ===== --}}
      <aside class="bg-white rounded-2xl shadow-md h-full flex flex-col">
        <div class="p-5 border-b border-slate-200">
          <h3 class="font-semibold text-[18px] border-b pb-3 text-slate-900">Penilaian Produk</h3>

          <div class="mt-4 grid grid-cols-1 sm:grid-cols-[.8fr_1.2fr] gap-4 items-center">
            {{-- Kiri: angka besar + /5 + total review --}}
            <div class="flex flex-col items-center">
              <div class="flex items-end gap-1">
                <div class="text-5xl font-semibold leading-none">{{ $avg }}</div>
                <div class="text-slate-500 text-base mb-1">/ 5.0</div>
              </div>
              <div class="text-[16px] text-slate-500 mt-1">({{ $rawTotal }} Review)</div>
            </div>

            {{-- Kanan: distribusi bintang --}}
            @php
              // ambil nilai terbesar untuk patokan bar penuh
              $maxCount = max(1, max(array_values($dist)));
            @endphp

            <div class="space-y-2 w-full">
              @foreach ([5,4,3,2,1] as $star)
                @php
                  $count = (int)($dist[$star] ?? 0);
                  $w = $maxCount ? round($count / $maxCount * 100) : 0;
                @endphp

                <div class="flex items-center gap-2">
                  {{-- ikon bintang --}}
                  <i class="bi bi-star-fill text-amber-400 text-[16px]"></i>

                  {{-- angka rating (rata tengah) --}}
                  <div class="flex items-center justify-center w-4 text-[16px] text-slate-700">
                    {{ $star }}
                  </div>

                  {{-- bar progress --}}
                  <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-2 rounded-full bg-brand-green transition-all duration-300"
                        style="width: {{ $w }}%"></div>
                  </div>

                  {{-- jumlah ulasan (rata tengah juga) --}}
                  <div class="flex items-center justify-center w-6 text-[16px] text-slate-700">
                    {{ $count }}
                  </div>
                </div>
              @endforeach
            </div>

          </div>

          


          @auth
            <button type="button"
                    class="mt-5 w-full flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-brand-green text-white hover:bg-brand-greenDark transition text-center"
                    data-review-modal-open>
              <i class="bi bi-pencil"></i>
              <span>Tambah Review</span>
            </button>
          @else
            <button type="button"
                    class="mt-5 w-full flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-brand-green text-white hover:bg-brand-greenDark transition text-center"
                    data-auth-open="login">
              <i class="bi bi-box-arrow-in-right"></i>
              <span>Login untuk menulis review</span>
            </button>
          @endauth
        </div>

        {{-- ===== List review: ≤2 tanpa scroll; >2 tinggi = 2 kartu lalu scroll ===== --}}
        @php
          $reviewCount = is_countable($reviews) ? count($reviews)
                        : (method_exists($reviews,'count') ? $reviews->count() : 0);
        @endphp

        <div id="reviewsWrap"
            class="p-5 space-y-3 flex-1 min-h-0 {{ $reviewCount > 2 ? 'overflow-y-auto' : 'overflow-visible' }}"
            data-review-count="{{ $reviewCount }}">
          @forelse($reviews as $rv)
            @php
              $u = $rv->user;
              $displayName = $u?->name ?? ($rv->author_name ?: 'Pengguna');
              // selalu pakai accessor publik; sudah punya fallback ui-avatars
              $avatarUrl = $u?->avatar_url ?? ('https://ui-avatars.com/api/?name=' . urlencode($displayName) . '&background=E5E7EB&color=111827&size=128');
            @endphp

            <div class="review-card rounded-[14px] bg-white p-4 shadow-soft">
              {{-- Baris atas: avatar + info nama/rating/waktu sejajar --}}
              <div class="flex items-center gap-3">
                @if($avatarUrl)
                  <img
                    src="{{ $avatarUrl }}"
                    alt="{{ $displayName }}"
                    class="h-10 w-10 rounded-full object-cover shrink-0"
                    loading="lazy"
                    referrerpolicy="no-referrer"
                  />
                @else
                  <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 shrink-0">
                    {{ $initial }}
                  </div>
                @endif

                <div class="flex-1">
                  <div class="flex items-center justify-between flex-wrap gap-y-1">
                    <div>
                      <div class="font-semibold text-slate-900">{{ $displayName }}</div>
                      <div class="flex items-center gap-1 text-yellow-400 mt-[2px]">
                        @for ($i = 1; $i <= 5; $i++)
                          <i class="bi {{ $i <= (int)$rv->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                        @endfor
                      </div>
                    </div>
                    <div class="text-xs text-slate-500">{{ optional($rv->created_at)->diffForHumans() }}</div>
                  </div>
                </div>
              </div>

              {{-- Isi review --}}
              <div class="mt-3">
                <p class="text-sm text-slate-600 leading-relaxed break-all">
                  {{ $rv->body ?: 'Tidak ada komentar.' }}
                </p>
              </div>
            </div>
          @empty
            <p class="text-[16px] text-slate-500">Belum ada review untuk produk ini.</p>
          @endforelse

          @if(method_exists($reviews, 'links'))
            <div class="mt-4">
              {{ $reviews->links() }}
            </div>
          @endif
        </div>

      </aside>

      {{-- ===== Modal Tambah Review (login only) ===== --}}
      @auth
      <div class="fixed inset-0 z-50 hidden" id="reviewModal" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-black/40" data-review-modal-close></div>
        <div class="absolute inset-0 grid place-items-center p-4">
          <div class="w-full max-w-md rounded-[16px] bg-white p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-2">
              <h4 class="text-lg font-semibold text-slate-900">Kirim Ulasan Produk</h4>
              <button type="button" class="text-slate-500 hover:text-slate-700" data-review-modal-close>
                <i class="bi bi-x-lg"></i>
              </button>
            </div>

            {{-- identitas user --}}
            @php
              $me = auth()->user();
              $meAvatar = $me->avatar ?? null;
              $meAvatarUrl = $meAvatar
                ? (Str::startsWith($meAvatar, ['http://','https://']) ? $meAvatar : asset(ltrim($meAvatar,'/')))
                : asset('assets/img/avatar/placeholder-default.png');
            @endphp
            <div class="flex items-center gap-3 mb-3">
              <img src="{{ $meAvatarUrl }}" alt="{{ $me->name }}" class="h-9 w-9 rounded-full object-cover">
              <div class="text-sm text-slate-700">
                Menulis sebagai <span class="font-medium text-slate-900">{{ $me->name }}</span>
              </div>
            </div>

            @php
              // form jadi read-only bila user sudah pernah review produk ini
              $readonly = auth()->check() && !empty($alreadyReviewed) && $alreadyReviewed === true && isset($userReview);
              $initRating = old('rating', $readonly ? (int)($userReview->rating ?? 0) : 0);
            @endphp

            <form method="POST"
                  action="{{ $readonly ? '#' : route('products.reviews.store', $product) }}"
                  id="reviewForm"
                  @if($readonly) onsubmit="return false" @endif
                  data-readonly="{{ $readonly ? '1' : '0' }}">
              @csrf

              {{-- Jika halaman punya pilihan varian, isi hidden ini via JS --}}
              <input type="hidden" name="variant_id" id="reviewVariantId" value="">

              {{-- rating --}}
              <div class="mb-2">
                <div class="flex gap-2 text-2xl text-yellow-400" id="ratingStars">
                  @for ($i = 1; $i <= 5; $i++)
                    <button type="button"
                            class="star {{ $readonly ? 'pointer-events-none opacity-60' : '' }}"
                            data-star="{{ $i }}">
                      <i class="bi {{ $i <= $initRating ? 'bi-star-fill' : 'bi-star' }}"></i>
                    </button>
                  @endfor
                </div>
                <div class="text-slate-500 text-sm mt-1">
                  {{ $readonly ? 'Anda sudah memberi nilai.' : 'Klik untuk memberi nilai' }}
                </div>

                {{-- ketika readonly, tetap kirim nilai rating sebagai hidden untuk konsistensi,
                    tapi form tidak akan disubmit karena action "#" dan onsubmit=false --}}
                <input type="hidden" name="rating" id="ratingInput" value="{{ $initRating }}">
              </div>

              {{-- ulasan --}}
              <div class="mt-4">
                <label class="block text-slate-900 font-medium mb-1">Tulis Ulasan Tentang Produk Ini</label>
                <textarea name="body" rows="5" placeholder="Ceritakan pengalamanmu…"
                  class="w-full rounded-[12px] border border-slate-200 p-3 focus:border-brand-green focus:ring-0 {{ $readonly ? 'bg-slate-50 text-slate-500 cursor-not-allowed' : '' }}"
                  {{ $readonly ? 'readonly disabled' : '' }}
                >{{ old('body', $readonly ? ($userReview->body ?? '') : '') }}</textarea>
              </div>

              <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="agree" value="1" class="rounded border-slate-300"
                      {{ $readonly ? 'disabled' : '' }}>
                Saya menyetujui syarat dan ketentuan
              </label>

              {{-- pesan error / info --}}
              @if ($errors->has('duplicate_review'))
                <div class="mt-3 text-sm text-red-600">{{ $errors->first('duplicate_review') }}</div>
              @elseif ($errors->any() && !$readonly)
                <div class="mt-3 text-sm text-red-600">{{ $errors->first() }}</div>
              @endif

              @if ($readonly)
                <div class="mt-3 text-sm text-red-600">Anda sudah memberi review untuk produk ini.</div>
              @endif

              {{-- tombol submit: sembunyikan/disable bila readonly --}}
              <button type="submit"
                class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-[12px] bg-brand-green px-4 py-2 font-medium text-white hover:bg-brand-greenDark {{ $readonly ? 'opacity-60 cursor-not-allowed' : '' }}"
                {{ $readonly ? 'disabled aria-disabled=true' : '' }}>
                Kirim ulasan produk
              </button>
            </form>

          </div>
        </div>
      </div>
    @endauth

    @push('scripts')
    <script>
    (() => {
      // ===== Modal: open / close =====
      const modal = document.getElementById('reviewModal');
      document.querySelectorAll('[data-review-modal-open]').forEach(el => {
        el.addEventListener('click', () => modal?.classList.remove('hidden'));
      });
      document.querySelectorAll('[data-review-modal-close]').forEach(el => {
        el.addEventListener('click', () => modal?.classList.add('hidden'));
      });

      // ===== Star rating =====
      const starsWrap   = document.getElementById('ratingStars');
      const ratingInput = document.getElementById('ratingInput');
      if (starsWrap && ratingInput) {
        const paint = n => starsWrap.querySelectorAll('.star i').forEach((icon, idx) => {
          icon.className = (idx < n) ? 'bi bi-star-fill' : 'bi bi-star';
        });
        starsWrap.addEventListener('click', e => {
          const btn = e.target.closest('.star'); if (!btn) return;
          const val = parseInt(btn.dataset.star, 10) || 0;
          ratingInput.value = val; paint(val);
        });
        paint(parseInt(ratingInput.value || '0', 10));
      }

      // ===== Validasi submit review =====
      const form = document.getElementById('reviewForm');
      if (form) form.addEventListener('submit', e => {
        const val = parseInt((document.getElementById('ratingInput')?.value) || '0', 10);
        if (val < 1) { e.preventDefault(); alert('Pilih rating terlebih dahulu.'); }
      });

        // ===== List Review: tinggi = 2 kartu pertama, >2 baru scroll =====
        const wrap = document.getElementById('reviewsWrap');
        if (wrap) {
        const count = parseInt(wrap.dataset.reviewCount || '0', 10);
        const GAP = 15; // space-y-5 = 1.25rem

        const setMaxHeight = () => {
          if (count <= 2) {
            wrap.style.maxHeight = '';
            wrap.classList.remove('overflow-y-auto');
            wrap.classList.add('overflow-visible');
            return;
          }
          const cards = wrap.querySelectorAll('.review-card');
          if (cards.length < 2) return;

          const h1 = cards[0].getBoundingClientRect().height;
          const h2 = cards[1].getBoundingClientRect().height;
          const cs = getComputedStyle(wrap);
          const pTop = parseFloat(cs.paddingTop) || 0;
          const pBottom = parseFloat(cs.paddingBottom) || 0;

          const extraBottom = 12;
          const max = Math.ceil(h1 + h2 + GAP + pTop + pBottom + extraBottom);
          wrap.style.maxHeight = max + 'px';

          // wrap.style.maxHeight = Math.ceil(h1 + h2 + GAP + pTop + pBottom) + 'px';
          wrap.classList.add('overflow-y-auto');
          wrap.classList.remove('overflow-visible');
        };

        setMaxHeight();
        window.addEventListener('resize', setMaxHeight);
        wrap.querySelectorAll('img').forEach(img => {
          if (!img.complete) img.addEventListener('load', setMaxHeight, { once: true });
        });
      }

      // OPTIONAL: jika ada selector varian, isi hidden variant_id di modal
      // document.querySelectorAll('[data-variant-radio]').forEach(r=>{
      //   r.addEventListener('change', ()=> document.getElementById('reviewVariantId').value = r.value);
      // });

      window.addEventListener('load', function () {
        if (!window.jQuery) return;
        $('#detailPageOwl').owlCarousel({
          loop: {{ isset($carouselProducts) && $carouselProducts->count() > 4 ? 'true' : 'false' }},
          margin: 16,
          dots: true,
          nav: false,
          smartSpeed: 450,
          stagePadding: 0,
          responsive: {
            0:    { items: 1, margin: 12 },
            640:  { items: 2, margin: 14 },
            1024: { items: 3, margin: 16 },
            1280: { items: 4, margin: 20 }
          }
        });
      });
    })();
    </script>
    @endpush



    </div>
    
    {{-- ================= RELATED CAROUSEL: sama seperti home ================= --}}
    @if($carouselProducts->count())
    <section class="py-12 bg-white">
      <div class="container">
        <div class="flex flex-wrap items-center justify-between mb-6">
          <div>
            <p class="text-[18px] text-slate-500 ">{{ $carouselSubtitle }}</p>
            <h2 class="text-[32px] lg:text-[46px] font-medium text-slate-900 leading-snug">
              {{ $carouselTitle }}
            </h2>
          </div>
        </div>

        <div id="detailPageOwl" class="product-owl owl-carousel">
          @foreach ($carouselProducts as $p)
            @include('partials.product-card', ['p' => $p, 'from' => $carouselFrom])
          @endforeach
        </div>
      </div>
    </section>
    @endif

  </div>
</section>
@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const boot = () => {
      if (!window.jQuery || !jQuery.fn.owlCarousel) return setTimeout(boot, 40);

      // Sama seperti di home: product-owl dengan items 1.2 / 2.2 / 3.5
      $('#detailPageOwl').owlCarousel({
        loop: {{ isset($carouselProducts) && $carouselProducts->count() > 4 ? 'true' : 'false' }},
        nav: false,
        dots: true,
        margin: 0,         // jarak antar kartu diatur oleh padding/mx di card
        smartSpeed: 450,
        mouseDrag: true,
        touchDrag: true,
        stagePadding: 0,
        responsive: {
          0:    { items: 1.2 },
          640:  { items: 2.2 },
          1024: { items: 3.5 },
          1280: { items: 3.5 }
        }
      });
    };
    boot();
  });
</script>
@endpush

@push('scripts')
<script>
(function () {
  const token = document.querySelector('meta[name="csrf-token"]')?.content;
  const badge = document.getElementById('navCartBadge');

  async function addToCart(productId, qty) {
    const res = await fetch(`{{ url('/cart/add') }}/${productId}`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
      body: new URLSearchParams({ qty: String(qty || 1) })
    });
    if (!res.ok) throw new Error('Request gagal');

    const data = await res.json();
    if (!data.ok) throw new Error('Gagal menambahkan');

    if (badge && typeof data.cart_count !== 'undefined') {
      badge.textContent = data.cart_count;
    }

    // === Efek zoom pada badge keranjang (sinkron dgn card carousel) ===
    if (badge) {
      badge.classList.add('transition-transform', 'duration-300');
      badge.classList.add('scale-125');
      setTimeout(() => badge.classList.remove('scale-125'), 300);
    }

    return data;
  }

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-add-to-cart]');
    if (!btn) return;

    // Abaikan tombol add-to-cart yang berada di dalam kartu carousel
    // agar handler di halaman detail tidak dobel request dengan handler kartu.
    if (btn.closest('[data-product-card]')) return;

    e.preventDefault();

    const productId = btn.getAttribute('data-product-id');
    const qtySel    = btn.getAttribute('data-qty-el');
    const qtyEl     = qtySel ? document.querySelector(qtySel) : null;

    // Dukung <input> (value) maupun elemen teks (textContent)
    const rawQty = qtyEl
      ? ((qtyEl.value !== undefined ? qtyEl.value : qtyEl.textContent) || '1')
      : '1';
    const qty = Math.max(1, parseInt(rawQty, 10) || 1);

    try {
      await addToCart(productId, qty);

      // Feedback singkat pada tombol (sesuai versi kamu)
      btn.classList.add('ring-2','ring-brand-green/60');
      setTimeout(()=>btn.classList.remove('ring-2','ring-brand-green/60'), 220);
    } catch {
      // kalau belum login, buka modal login (dari skrip auth mu)
      if (window.openAuthModal) window.openAuthModal('login');
      else alert('Silakan login terlebih dahulu.');
    }
  });
})();
</script>
@endpush




<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection

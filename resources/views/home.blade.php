@extends('layouts.app')

@section('content')
<section class="relative overflow-hidden bg-[#f5f5f5]">
  <div class="container grid items-center gap-8 pt-[64px] pb-[0px]  lg:grid-cols-2">
    {{-- Kiri: teks --}}
    <div>
      <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-white shadow-soft px-[18px] py-[12px] text-sm">
        <span class="inline-flex h-[30px] w-[30px] items-center justify-center rounded-full bg-brand-green/10 text-brand-green">
          <i class="bi bi-suitcase2-fill text-[16px]"></i>
        </span>
        <span class="text-[18px]">Lorem ipsum dolor sit amet</span>
      </div>

      <h1 class=" text-[32px] lg:text-[46px] font-medium leading-normal text-slate-900  ">
        Lorem ipsum dolor sit<br class="hidden sm:block"> amet consectetur.
      </h1>

      <p class="mt-[32px] max-w-xl text-slate-600 text-[18px] ">
        Lorem ipsum dolor sit amet consectetur. Curabitur faucibus pellentesque orci lectus nibh hendrerit faucibus velit tellus. 
        Elit enim rhoncus pharetra mauris mi massa quam ac.
      </p>

      <div class="mt-8 flex flex-wrap items-center gap-4">
        <a href="#" class="inline-flex items-center gap-2 rounded-[14px] bg-brand-green px-[50px] py-[12px] text-white shadow-soft hover:bg-brand-greenDark text-[18px]">
          Shop Now
          <i class="bi bi-arrow-right"></i>
        </a>
        <a href="#" class="font-semibold text-[18px] hover:underline">View All Products</a>
      </div>

      {{-- Avatars + rating --}}
      <div class="mt-8 flex items-center gap-5">
        <div class="flex -space-x-3">
          <img class="h-[50px] w-[50px] rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=1" alt="">
          <img class="[50px] w-[50px] rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=2" alt="">
          <img class="[50px] w-[50px] rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=3" alt="">
          <img class="[50px] w-[50px] rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=3" alt="">
          <div class="flex [50px] w-[50px] items-center justify-center rounded-full border-2 border-white bg-grayish text-sm font-semibold">+</div>
        </div>
        <div>
          <div class="text-[18px] font-semibold">4.9 + Ratings</div>
          <div class="text-[16px] text-slate-500">Trusted by 95k+ Customers</div>
        </div>
      </div>
    </div>

    {{-- Kanan: gambar hero + badges --}}
    <div class="relative">
      {{-- Gambar: ganti dengan file kamu /public/images/hero.png --}}
      <div class="relative mx-auto max-w-xl">
        <img class="w-full pt-[40px] object-cover " src="{{ asset('assets/img/hero.png') }}" alt="Hero">
      </div>

    </div>
  </div>
</section>

{{-- ===================== PRODUCTS (DB) ===================== --}}
@php
  use Illuminate\Support\Arr;
  use Illuminate\Support\Str;

  /** @var \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection|array $products */
  $list = collect($popularProducts ?? $products ?? [])->take(5);
@endphp

<section class="py-12 bg-white">
  <div class="container">
    <div class="flex flex-wrap items-center justify-between mb-6">
      <div>
        <p class="text-[18px] text-slate-500 ">Lorem ipsum</p>
        <h2 class="text-[32px] lg:text-[46px] font-medium text-slate-900 leading-snug">
          Lorem ipsum dolor sit
        </h2>
      </div>
      <a href="{{ route('products.filter', ['sort' => 'popular']) }}"
         class="inline-flex items-center gap-2 rounded-[14px] bg-brand-green px-5 py-3 font-[400] text-white shadow-soft hover:bg-brand-greenDark">
        View All Products
        <i class="bi bi-arrow-right"></i>
      </a>
    </div>

    @if ($list->isEmpty())
      <div class="rounded-[16px] bg-white p-10 text-center shadow-soft">
        <p class="text-slate-600">Belum ada produk untuk ditampilkan.</p>
      </div>
    @else
      <div id="dbProductsOwl" class="product-owl owl-carousel">
        @foreach ($list as $p)
          @include('partials.product-card', ['p' => $p, 'from' => 'popular'])
        @endforeach
      </div>
    @endif
  </div>
</section>

@push('scripts')
<script>
  window.addEventListener('load', function () {
    if (!window.jQuery) {
      console.error('jQuery belum siap');
      return;
    }
    $('#dbProductsOwl').owlCarousel({
      loop: {{ ($list ?? collect())->count() > 4 ? 'true' : 'false' }},
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
</script>
@endpush


{{-- ===== FEATURED WIDE CARDS ===== --}}
@php
  // Contoh data (ganti dari Controller/Dataset kamu)
  $featured = [
    [
      'title' => 'RESIN BENING 500 Gram + KATALIS 35 ML',
      'desc'  => 'Lorem ipsum dolor sit amet consectetur. Quis potenti nulla.',
      'price' => 55000,
      'rating'=> 4.9,
      'img'   => asset('assets/img/resin.png'),
      'tag'   => 'Lorem ipsum',
      'discount' => '50% off',
    ],
    [
      'title' => 'RESIN BENING 500 Gram + KATALIS 35 ML',
      'desc'  => 'Lorem ipsum dolor sit amet consectetur. Ut malesuada nam pellentesque faucibus eget volutpat.',
      'price' => 55000,
      'rating'=> 4.9,
      'img'   => asset('assets/img/resin.png'),
      'tag'   => 'Lorem ipsum',
      'discount' => '50% off',
    ],
   
  ];
@endphp

<section class="bg-white lg:py-0">
  <div class="container">
    {{-- Header --}}
    <div class="grid gap-6 lg:grid-cols-2 mb-6 lg:mb-8">
      {{-- Kolom kiri --}}
      <div>
        <p class="text-[18px] text-slate-500">Lorem ipsum</p>
        <h2 class="text-[32px] lg:text-[46px] font-medium leading-snug text-slate-900">
          Lorem ipsum dolor sit
        </h2>
      </div>

      {{-- Kolom kanan — teks kanan & tengah vertikal --}}
      <div class="flex items-center justify-end">
        <p class="text-slate-500 max-w-[52ch] text-[18px]">
          Lorem ipsum dolor sit amet consectetur. Ut malesuada nam pell entesque faucibus eget volutpat
        </p>
      </div>
    </div>

    {{-- Grid 2 kolom (card lebar) --}}
    <div class="grid gap-6 lg:grid-cols-2">
      @foreach ($featured as $f)
      <article class="rounded-[20px] bg-white p-3 sm:p-4 lg:p-5 shadow-soft overflow-hidden">
        {{-- Gunakan flex-row alih-alih grid untuk tinggi dinamis --}}
        <div class="flex flex-col sm:flex-row gap-4">
          
          {{-- Image --}}
          <div class="relative flex-shrink-0 w-full sm:w-[280px]">
            <img src="{{ $f['img'] }}" alt="{{ $f['title'] }}"
                class="w-full h-full sm:h-full object-cover rounded-[16px]">
            {{-- Badge --}}
            <span class="absolute left-2 top-2 rounded-md bg-brand-green px-2 py-1 text-xs font-semibold text-white">
              {{ $f['discount'] }}
            </span>
            {{-- Wishlist --}}
            <button class="absolute right-2 top-2 inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-slate-700 shadow-soft">
              <i class="bi bi-heart"></i>
            </button>
            
          </div>

          {{-- Content --}}
          <div class="flex flex-col flex-1">
            <div class="flex items-center justify-between mb-1">
              <span class="text-brand-green text-[16px] font-[400]">{{ $f['tag'] }}</span>
              <span class="inline-flex items-center gap-1 text-yellow-400 text-[16px]">
                <i class="bi bi-star-fill"></i>
                <span class="text-slate-700 font-[500] text-[16px]">{{ number_format($f['rating'],1) }}</span>
              </span>
            </div>

            <h3 class="text-[18px] font-[500] mt-2 leading-snug text-slate-900">
              {{ $f['title'] }}
            </h3>

            <p class="mt-2 text-slate-500 line-clamp-2">
              {{ $f['desc'] }}
            </p>

            
            <div class="mt-3 flex items-center gap-2 flex-1 justify-between">
              <div class=" text-slate-500">Kuantitas</div>
              <div class="flex items-center gap-2 rounded-[12px] bg-slate-100 px-3 py-1">
                <button class="h-7 w-7 flex items-center justify-center rounded-md text-slate-700 hover:bg-gray-50">−</button>
                <span class="min-w-[1.5rem] text-center font-medium text-slate-800">4</span>
                <button class="h-7 w-7 flex items-center justify-center rounded-md text-slate-700 hover:bg-gray-50">+</button>
              </div>

              
            </div>
            <div class="mt-3 flex items-center justify-between">
                  <div class="text-[18px] font-[500] text-slate-900">Rp {{ number_format($f['price'] ?? 0, 0, ',', '.') }}</div>
                  <button class=" px-[24px] inline-flex items-center justify-center gap-2 rounded-[12px] bg-brand-green/10 py-1 font-medium text-brand-green hover:bg-brand-green/15">
                  <i class="bi bi-handbag text-[20px]"></i>
                  </button>
            </div>
          </div>
        </div>
      </article>

      @endforeach
    </div>
  </div>
</section>

{{-- ===== PROMO BANNERS ===== --}}
<section class="py-10 lg:py-14 mt-5 bg-white">
  <div class="container">
    <div class="grid gap-6 lg:grid-cols-2">

      {{-- === Card kiri (Putih) === --}}
      <article class="relative flex items-center overflow-hidden rounded-[24px] bg-white p-6 lg:p-0 shadow-soft">
        {{-- Content kiri --}}
        <div class="relative z-10 flex-1 px-6 py-6 lg:py-8">
          <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-1 text-[18px] font-[400] text-slate-700">
            Flat 20% Discount
          </span>

          <h3 class="mt-4 text-[28px] lg:text-[32px] font-semibold leading-tight text-slate-900">
            Lorem ipsum <br class="hidden sm:block"/> dolor sit d
          </h3>

          <p class="mt-3 max-w-[36ch] text-slate-500">
            Lorem ipsum dolor sit amet consectetur. Ipsum massa vitae nec ut est feugiat eu.
          </p>

          <a href="#"
             class="mt-5 inline-flex items-center gap-2 rounded-[12px] bg-brand-green px-5 py-3 font-semibold text-white hover:bg-brand-greenDark shadow-soft">
            Shop Now
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>

        {{-- Gambar kanan (center vertikal & mepet kanan) --}}
        <div class="flex-shrink-0 flex items-center justify-end h-full mr-0">
          <img src="{{ asset('assets/img/promo-lab.png') }}"
               alt="Promo Lab"
               class="h-full max-h-[260px] w-full object-contain">
        </div>
      </article>

      {{-- === Card kanan (Hijau) === --}}
      <article class="relative flex items-center overflow-hidden rounded-[24px] bg-brand-green p-6 lg:p-0 shadow-soft">
        {{-- Content kiri --}}
        <div class="relative z-10 flex-1 px-6 py-6 lg:py-8">
          <span class="inline-flex items-center rounded-full bg-white/15 px-4 py-1 text-[18px] font-[400] text-white/90 ring-1 ring-white/20">
            Flat 20% Discount
          </span>

          <h3 class="mt-4 text-[28px] lg:text-[32px] font-semibold leading-tight text-white">
            Lorem ipsum <br class="hidden sm:block"/> dolor sit d
          </h3>

          <p class="mt-3 max-w-[36ch] text-white/85">
            Lorem ipsum dolor sit amet consectetur. Ipsum massa vitae nec ut est feugiat.
          </p>

          <a href="#"
             class="mt-5 inline-flex items-center gap-2 rounded-[12px] bg-[#F9C71A] px-5 py-3 font-semibold text-white hover:bg-[#F9C71A] hover:text-brand-green shadow-soft">
            Shop Now
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>

        {{-- Gambar kanan (center vertikal & mepet kanan) --}}
        <div class="flex-shrink-0 flex items-center justify-end h-full mr-0">
          <img src="{{ asset('assets/img/promo-cleaning.png') }}"
               alt="Promo Cleaning"
               class="h-full max-h-[260px] w-[200px] object-contain">
        </div>
      </article>

    </div>
  </div>
</section>

{{-- ================= CAROUSEL ================= --}}
@php
  $listNewest = collect($newestProducts ?? [])->take(5);
  $listBestseller = collect($bestsellerProducts ?? [])->take(5);
@endphp
<section class="py-12 bg-white">
  <div class="container">
    <div class="flex flex-wrap items-center justify-between ">
      <div>
        <p class="text-[18px] text-slate-500 ">Produk Terbaru</p>
        <h2 class="text-[32px] lg:text-[46px] font-medium text-slate-900 leading-snug">
          Baru Rilis
        </h2>
      </div>
      <a href="{{ route('products.filter', ['sort' => 'newest']) }}"
         class="inline-flex items-center gap-2 rounded-[14px] bg-brand-green px-5 py-3 font-[400] text-white shadow-soft hover:bg-brand-greenDark">
        Lihat Semua Terbaru
        <i class="bi bi-arrow-right"></i>
      </a>
    </div>

    {{-- TERBARU --}}
    @if ($listNewest->isEmpty())
      <div class="rounded-[16px] bg-white p-10 text-center shadow-soft">
        <p class="text-slate-600">Belum ada produk.</p>
      </div>
    @else
      <div id="dbNewestOwl" class="product-owl owl-carousel">
        @foreach ($listNewest as $p)
          @include('partials.product-card', ['p' => $p, 'from' => 'newest'])
        @endforeach
      </div>
    @endif

    {{-- TERLARIS --}}
    @if ($listBestseller->isEmpty())
      <div class="rounded-[16px] bg-white p-10 text-center shadow-soft">
        <p class="text-slate-600">Belum ada produk.</p>
      </div>
    @else
      <div id="dbBestsellerOwl" class="product-owl owl-carousel">
        @foreach ($listBestseller as $p)
          @include('partials.product-card', ['p' => $p, 'from' => 'bestseller'])
        @endforeach
      </div>
    @endif
  </div>
</section>


{{-- ===== TESTIMONIALS ===== --}}
@php
  $testimonials = [
    [
      'name' => 'Selena',
      'role' => 'Youtuber',
      'rating' => 5.0,
      'text' => 'LuminaSkyn has truly transformed my skincare routine. The combination of science and nature delivers real, visible results. I love how luxurious yet subtle the products are—elegant without being over the top',
      'avatar' => asset('assets/img/avatar/avatar-1.png'),
    ],
    [
      'name' => 'Citra',
      'role' => 'Makeup Artist',
      'rating' => 5.0,
      'text' => 'LuminaSkyn has completely changed my skincare routine. The blend of science and nature provides noticeable, real results. I appreciate how luxurious yet understated the products are elegant without excess.',
      'avatar' => asset('assets/img/avatar/avatar-2.png'),
    ],
    // duplikasi biar carousel terasa panjang
    ...array_fill(0, 4, [
      'name' => 'Selena',
      'role' => 'Youtuber',
      'rating' => 5.0,
      'text' => 'LuminaSkyn has truly transformed my skincare routine. The combination of science and nature delivers real, visible results.',
      'avatar' => asset('assets/img/avatar/avatar-3.png'),
    ]),
  ];
@endphp

<section class="py-12 lg:py-16 bg-[#F5F5F5]">
  <div class="container">
    <div class="text-center mb-8 lg:mb-10">
      <p class="text-slate-500 text-[18px]">Testimonial</p>
      <h2 class="text-[28px] lg:text-[46px] font-[500] text-slate-900">
        What Our Clients Say
      </h2>
    </div>

    <div class="testimonial-owl owl-carousel">
      @foreach ($testimonials as $t)
      <div class="px-2 lg:px-3 py-5">
        <article class="flex flex-col justify-between h-full rounded-[16px] bg-white p-6 lg:p-7 shadow-[0_0_20px_rgba(0,0,0,0.04)]">
          {{-- TOP: rating + text --}}
          <div>
            {{-- Rating --}}
            <div class="mb-4 flex items-center gap-1">
              <i class="bi bi-star-fill text-yellow-400"></i>
              <i class="bi bi-star-fill text-yellow-400"></i>
              <i class="bi bi-star-fill text-yellow-400"></i>
              <i class="bi bi-star-fill text-yellow-400"></i>
              <i class="bi bi-star-fill text-yellow-400"></i>
              <span class="ml-2 text-slate-600 text-[16px]">{{ number_format($t['rating'],1) }}</span>
            </div>

            {{-- Text --}}
            <p class="text-slate-600 leading-relaxed">
              {{ $t['text'] }}
            </p>
          </div>

          {{-- BOTTOM: avatar + name + role --}}
          <div class="mt-6 flex items-center gap-3 pt-4  border-slate-100">
            <img src="{{ $t['avatar'] }}" alt="{{ $t['name'] }}"
                class="h-[60px] w-[60px] rounded-full object-cover">
            <div>
              <div class="font-[500] text-slate-800 text-[18px] leading-relaxed">{{ $t['name'] }}</div>
              <div class="text-slate-500 text-[16px]">{{ $t['role'] }}</div>
            </div>
          </div>
        </article>

      </div>
      @endforeach
    </div>
  </div>
</section>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const boot = () => {
      if (!window.jQuery || !jQuery.fn.owlCarousel) return setTimeout(boot, 40);

      $('.testimonial-owl').owlCarousel({
        loop: true,
        nav: false,
        dots: true,
        margin: 5,
        smartSpeed: 450,
        responsive: {
          0:   { items: 1, stagePadding: 12 },
          768: { items: 2, stagePadding: 0 }
        }
      });
    };
    boot();
  });
</script>
@endpush

{{-- ===== NEWS & BLOGS ===== --}}
@php
  // Contoh data (ganti dari DB/Controller kamu)
  $posts = [
    [
      'title'   => 'Lorem ipsum mauris cursus purus auctor',
      'excerpt' => 'Lorem ipsum mauris cursus purus auctor in pellentesque ut tempor elit. Lorem ipsum mauris cursus purus',
      'image'   => asset('assets/img/blog.png'),
      'url'     => '#',
    ],
    [
      'title'   => 'Lorem ipsum mauris cursus purus auctor',
      'excerpt' => 'Lorem ipsum mauris cursus purus auctor in pellentesque ut tempor elit. Lorem ipsum mauris cursus purus',
      'image'   => asset('assets/img/blog.png'),
      'url'     => '#',
    ],
    [
      'title'   => 'Lorem ipsum mauris cursus purus auctor',
      'excerpt' => 'Lorem ipsum mauris cursus purus auctor in pellentesque ut tempor elit. Lorem ipsum mauris cursus purus',
      'image'   => asset('assets/img/blog.png'),
      'url'     => '#',
    ],
  ];
@endphp

<section class="py-12 lg:py-16 bg-white">
  <div class="container">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between mb-6">
      <div>
        <p class="text-[18px] text-slate-500 ">News & Blogs</p>
        <h2 class="text-[32px] lg:text-[46px] font-medium text-slate-900 leading-snug">
          Our Latest News & Blogs
        </h2>
      </div>
      <a href="#" class="inline-flex items-center gap-2 rounded-[14px] bg-brand-green px-5 py-3 font-[400] text-white shadow-soft hover:bg-brand-greenDark">
        View all
        <i class="bi bi-arrow-right"></i>
      </a>
    </div>

    {{-- Grid posts --}}
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      @foreach ($posts as $post)
      <article class="group rounded-[16px] bg-white p-3 sm:p-4 shadow-soft transition hover:-translate-y-0.5">
        <a href="{{ $post['url'] }}" class="block overflow-hidden rounded-[12px]">
          <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}"
               class="h-48 w-full object-cover rounded-[12px] transition duration-300 ">
        </a>

        <div class="mt-4">
          <a href="{{ $post['url'] }}">
            <h3 class="text-slate-900 font-[500] text-[18px] leading-snug">
              {{ $post['title'] }}
            </h3>
          </a>

          <p class="mt-3 text-slate-500 leading-relaxed text-[16px] line-clamp-2">
            {{ $post['excerpt'] }}
          </p>

          <a href="{{ $post['url'] }}"
             class="mt-4 inline-flex items-center gap-2 rounded-[10px] bg-brand-green px-4 py-2.5 font-medium text-white hover:bg-brand-green/90">
            Read More
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </article>
      @endforeach
    </div>
  </div>
</section>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const boot = () => {
    if (!window.jQuery || !jQuery.fn.owlCarousel) return setTimeout(boot, 40);

    $('.product-owl').owlCarousel({
      loop: true,         // geser terus
      nav: false,
      dots: true,
      margin: 0,         // jarak antar kartu
      smartSpeed: 450,
      mouseDrag: true,
      touchDrag: true,
      stagePadding: 0,    // padding kanan kita atur via CSS (bukan Owl)
      responsive: {
        0:    { items: 1.2 },
        640:  { items: 2.2 },
        1024: { items: 3.5   },  // desktop: 3 penuh
        1280: { items: 3.5   }
      }
    });
  };
  boot();
});
</script>
@endpush
@push('scripts')
<script>
  window.addEventListener('load', function () {
    if (!window.jQuery) {
      console.error('jQuery belum siap');
      return;
    }
    // Popular
    $('#dbProductsOwl').owlCarousel({
      loop: {{ (collect($popularProducts ?? [])->count() > 4) ? 'true' : 'false' }},
      margin: 16, dots: true, nav: false, smartSpeed: 450, stagePadding: 0,
      responsive: { 0:{items:1,margin:12},640:{items:2,margin:14},1024:{items:3,margin:16},1280:{items:4,margin:20} }
    });
    // Newest
    $('#dbNewestOwl').owlCarousel({
      loop: {{ (collect($newestProducts ?? [])->count() > 4) ? 'true' : 'false' }},
      margin: 16, dots: true, nav: false, smartSpeed: 450, stagePadding: 0,
      responsive: { 0:{items:1,margin:12},640:{items:2,margin:14},1024:{items:3,margin:16},1280:{items:4,margin:20} }
    });
    // Bestseller
    $('#dbBestsellerOwl').owlCarousel({
      loop: {{ (collect($bestsellerProducts ?? [])->count() > 4) ? 'true' : 'false' }},
      margin: 16, dots: true, nav: false, smartSpeed: 450, stagePadding: 0,
      responsive: { 0:{items:1,margin:12},640:{items:2,margin:14},1024:{items:3,margin:16},1280:{items:4,margin:20} }
    });
  });
</script>
@endpush
@endsection

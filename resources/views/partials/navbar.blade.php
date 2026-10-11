{{-- TOP BAR (kuning) --}}
<div id="topBar"
     class="fixed inset-x-0 top-0 z-[60] bg-brand-yellow text-slate-900 text-sm
            translate-y-0 transition-transform duration-300 ease-linear will-change-transform">
  <div class="container flex items-center justify-between py-2">
    {{-- Telp kiri --}}
    <div class="flex items-center gap-2">
      <i class="bi bi-telephone text-[#292929] "></i>
      <span class="text-[16px] text-[#292929]">+62 8780-8401-727</span>
    </div>

    {{-- Promo center --}}
    <div class="hidden md:block text-[16px] text-[#292929]">
      Diskon 25% untuk pesanan pertamamu.
      @guest
        <a id="linkTopbarSignup" href="#" class="underline font-semibold text-[green]">Sign up</a>
      @endguest
    </div>

    {{-- Social kanan --}}
    <div class="hidden md:flex items-center gap-3">
      <a href="#" class="icon-btn text-slate-700 rounded-full bg-brand-green shadow-soft" aria-label="YouTube">
        <i class="bi bi-instagram"></i>
      </a>
      <a href="#" class="icon-btn text-slate-700 rounded-full bg-brand-green shadow-soft" aria-label="Instagram">
        <i class="bi bi-facebook"></i>
      </a>
      <a href="#" class="icon-btn text-slate-700 rounded-full bg-brand-green shadow-soft" aria-label="Tiktok">
        <i class="bi bi-tiktok"></i>
      </a>
      <a href="#" class="icon-btn text-slate-700 rounded-full bg-brand-green shadow-soft" aria-label="Tiktok">
        <i class="bi bi-twitter-x"></i>
      </a>
    </div>
  </div>
</div>

{{-- NAVBAR UTAMA (hijau) --}}
<header id="mainNav"
        class="sticky z-[50] bg-brand-green"
        style="top: var(--nav-offset, 40px);">
  <div class="container">
    {{-- ROW: logo + search pill + icons --}}
    <div class="flex items-center gap-5 pt-5 pb-4">
      {{-- Logo kiri --}}
      <a href="{{ route('home') }}" class="flex items-center gap-3 text-white shrink-0">
        <span class="inline-flex h-[50px] w-[50px] items-center justify-center rounded-full bg-brand-yellow">
          <img class="  object-cover " src="{{ asset('assets/img/logo.png') }}" alt="Hero">
            <circle cx="12" cy="12" r="9"></circle><path d="M6 12h12M12 6v12"></path>
          </svg>
        </span>
        <span class="text-[18px] ">Megah Abadi.</span>
      </a>

      {{-- Pill search tengah (lebarnya besar, mirip desain) --}}
      <div class="hidden lg:flex flex-1 justify-center">
        <div class="search-pill">
          {{-- dropdown kategori --}}
          <div class="relative shrink-0">
            <button data-dropdown-btn="#ddKategori" class="pill-btn">
              <span class="text-[16px] ">Semua Kategori</span>
              <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none">
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5"/>
              </svg>
            </button>
            <div id="ddKategori" class="dropdown">
              <a class="dropdown-item" href="#">Pembersih</a>
              <a class="dropdown-item" href="#">Alat Rumah</a>
              <a class="dropdown-item" href="#">Lainnya</a>
            </div>
          </div>

          <div class="h-10 w-px bg-white/15"></div>

          {{-- input search --}}
          <input class="flex-1 bg-transparent px-4 text-white placeholder-white/60 focus:outline-none"
                 placeholder="Cari Produk" />

          <button class="rounded-full py-2 px-3 text-white/90 hover:bg-white/10">
            <i class="bi bi-search"></i>
          </button>
        </div>
      </div>

      {{-- Ikon kanan --}}
      @php
        use Illuminate\Support\Facades\Storage;
        use Illuminate\Support\Str;
      @endphp

      <div class="hidden md:flex items-center gap-1 ml-auto">
        @php
          // total qty keranjang untuk user login (0 kalau guest)
          $navCartCount = auth()->check()
              ? \App\Models\CartItem::whereHas('cart', fn($q)=>$q->where('user_id', auth()->id()))->sum('qty')
              : 0;
        @endphp

        {{-- NAVBAR: tombol keranjang + badge --}}
        @auth
          <a class="icon-btn relative" href="{{ route('cart.index') }}" aria-label="Cart">
            <i class="bi bi-handbag"></i>
            <span id="navCartBadge"
                  class="absolute -top-1 -right-2 min-w-[18px] h-[18px] px-[5px] rounded-full bg-white text-[black] text-[11px] leading-[18px] text-center">
              0
            </span>
          </a>
        @else
          <button type="button"
                  class="relative inline-flex items-center justify-center icon-btn"
                  data-auth-open="login" aria-label="Keranjang (login dulu)">
            <i class="bi bi-handbag text-[20px]"></i>
            <span id="navCartBadge"
                  class="absolute -top-1 -right-2 min-w-[18px] h-[18px] rounded-full bg-brand-green text-white
                        text-[11px] px-1 leading-[18px] text-center">
              0
            </span>
          </button>
        @endauth

        <a class="icon-btn" href="#" aria-label="Chat">
          <i class="bi bi-chat-text"></i>
        </a>
        <a class="icon-btn" href="#" aria-label="Favorite">
          <i class="bi bi-heart"></i>
        </a>

        @guest
          {{-- Guest: ikon user punya dropdown "Daftar" & "Login" --}}
          <div id="guestMenuWrap" class="relative">
            <button id="guestBtn" type="button" class="icon-btn" aria-label="User">
              <i class="bi bi-person"></i>
            </button>

            <div id="guestMenu"
                class="absolute right-0 z-10 mt-3 w-56 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 hidden">
              <div class="p-2">
                <button id="guestOpenDaftar" type="button"
                        class="w-full text-left rounded-xl px-3 py-2 hover:bg-brand-green hover:text-white">
                  Daftar
                </button>
                <button id="guestOpenLogin" type="button"
                        class="mt-1 w-full text-left rounded-xl px-3 py-2 hover:bg-brand-green hover:text-white">
                  Login
                </button>
              </div>
            </div>
          </div>
        @endguest


        @auth
          {{-- Logged-in: avatar + dropdown --}}
          @php
            $user = auth()->user(); 
            $raw  = $user->avatar ?? null;
            $defaultAvatar = asset('assets/img/avatar/placeholder-default.png');
            $avatarUrl = $raw
                ? (Str::startsWith($raw, ['http://','https://']) ? $raw : Storage::url($raw))
                : $defaultAvatar;
          @endphp
        <div class="mx-3 h-6 w-px bg-white/20"></div>

          <div id="profileMenu" class="relative ml-2">
            <button id="btnProfile" type="button"
                    class="flex items-center justify-center rounded-full p-[2px] ring-1 ring-white/20 hover:ring-white/40 focus:outline-none align-middle">
              <img src="{{ $avatarUrl }}" alt="{{ $user->name ?? 'Profile' }}"
                  class="h-[45px] w-[45px] rounded-full object-cover" loading="lazy">
            </button>

            <div id="menuProfile"
                class="absolute right-0 mt-3 w-64 z-10 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 hidden">
              <div class="p-2">
                <a href="{{ route('home') }}#orders"
                  class="mt-2 flex items-center gap-3 rounded-xl px-3 py-2 text-slate-700 hover:bg-brand-green hover:text-white">
                  <i class="bi bi-person "></i>
                  <span>Akun Saya</span>
                </a>

                <a href="{{ route('orders.index', ['tab' => 'all']) }}"
                  class="mt-2 flex items-center gap-3 rounded-xl px-3 py-2 text-slate-700 hover:bg-brand-green hover:text-white">
                  <i class="bi bi-handbag "></i>
                  <span>Pesanan</span>
                </a>

                <a href="{{ route('home') }}#vouchers"
                  class="mt-1 flex items-center gap-3 rounded-xl px-3 py-2 text-slate-700 hover:bg-brand-green hover:text-white">
                  <i class="bi bi-ticket-perforated "></i>
                  <span>Voucher</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                  @csrf
                  <button type="submit"
                          class="w-full flex items-center gap-3 rounded-xl px-3 py-2 text-left text-slate-700 hover:bg-brand-green hover:text-white">
                    <i class="bi bi-box-arrow-right "></i>
                    <span>Logout</span>
                  </button>
                </form>
              </div>
            </div>
          </div>
        @endauth
      </div>

      {{-- Mobile toggle --}}
      <button id="menuBtn" class="md:hidden rounded-lg p-2 text-white hover:bg-white/10 ml-auto">
        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>

    {{-- ROW: links + bahasa kanan (sejajar seperti desain) --}}
    <div class="flex items-center justify-between pb-4">
      <nav class="hidden md:flex items-center gap-8">
        <a href="#" class="nav-link">Beranda</a>
        <div class="relative">
          <button data-dropdown-btn="#ddProduk" class="nav-link inline-flex items-center gap-1">
            Produk
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
              <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5"/>
            </svg>
          </button>
          <div id="ddProduk" class="dropdown">
            <a class="dropdown-item" href="#">Semua Produk</a>
            <a class="dropdown-item" href="#">Promo</a>
          </div>
        </div>
        <a href="#" class="nav-link">Tentang Kami</a>
        <a href="#" class="nav-link">Blog</a>
        <a href="#" class="nav-link">Kontak</a>
      </nav>

      <div class="hidden md:flex items-center">
        <a class="icon-btn" href="#" aria-label="Favorite">
          <i class="bi bi-bell"></i>
        </a>
        <div class="mx-3 h-6 w-px bg-white/20"></div>
        <div class="relative">
          <button data-dropdown-btn="#ddLang" class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-white hover:bg-white/10">
            Bahasa Indonesia
            <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none">
              <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5"/>
            </svg>
          </button>
          <div id="ddLang" class="dropdown right-0">
            <a class="dropdown-item" href="#">Bahasa Indonesia</a>
            <a class="dropdown-item" href="#">English</a>
          </div>
        </div>
      </div>
    </div>

    {{-- MOBILE MENU --}}
    <div id="mobileMenu" class="md:hidden hidden pb-4">
      <div class="mb-3">
        <div class="flex w-full items-center rounded-full bg-brand-greenDark/60 px-3 ring-1 ring-white/10">
          <input type="text" class="w-full bg-transparent px-2 py-2 text-white placeholder-white/70 focus:outline-none" placeholder="Cari Produk" />
          <button class="rounded-full p-2 text-white hover:bg-white/10">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M20 20l-3-3" stroke="currentColor" stroke-width="1.5"/></svg>
          </button>
        </div>
      </div>
      <nav class="grid gap-2">
        <a href="#" class="rounded-xl bg-brand-greenDark/50 px-4 py-3 text-white">Beranda</a>
        <a href="#" class="rounded-xl bg-brand-greenDark/50 px-4 py-3 text-white">Produk</a>
        <a href="#" class="rounded-xl bg-brand-greenDark/50 px-4 py-3 text-white">Tentang Kami</a>
        <a href="#" class="rounded-xl bg-brand-greenDark/50 px-4 py-3 text-white">Blog</a>
        <a href="#" class="rounded-xl bg-brand-greenDark/50 px-4 py-3 text-white">Kontak</a>
      </nav>
    </div>
  </div>
</header>
<footer class="bg-brand-green text-white pt-12 pb-3 relative">
  <div class="container">
    {{-- === Baris 1: Logo & Deskripsi === --}}
    <div class="mb-10">
      <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('home') }}" class="flex items-center gap-3 text-white shrink-0">
        <span class="inline-flex h-[50px] w-[50px] items-center justify-center rounded-full bg-brand-yellow">
          <img class="  object-cover " src="{{ asset('assets/img/logo.png') }}" alt="Hero">
            <circle cx="12" cy="12" r="9"></circle><path d="M6 12h12M12 6v12"></path>
          </svg>
        </span>
        <span class="text-[18px] ">Megah Abadi.</span>
      </a>
      </div>
      <p class="text-white/90 leading-relaxed max-w-[40ch]">
        Lorem ipsum dolor sit amet consectetur. Scelerisque platea dui velit felis vulputate fermentum convallis semper.
      </p>
    </div>

    {{-- === Baris 2: 3 kolom link + 1 kolom contact === --}}
    <div class="grid gap-4 md:grid-cols-4">
      {{-- Information --}}
      <div>
        <h4 class="font-[500] text-[20px] mb-4">Information</h4>
        <ul class="space-y-2 text-white/90">
          <li><a href="#" class="hover:text-yellow-300 transition">FAQs</a></li>
          <li><a href="#" class="hover:text-yellow-300 transition">Terms & Conditions</a></li>
          <li><a href="#" class="hover:text-yellow-300 transition">Privacy Policy</a></li>
        </ul>
      </div>

      {{-- Get To Know Us --}}
      <div>
        <h4 class="font-[500] text-[20px] mb-4">Get To Know Us</h4>
        <ul class="space-y-2 text-white/90">
          <li><a href="#" class="hover:text-yellow-300 transition">Homepage</a></li>
          <li><a href="#" class="hover:text-yellow-300 transition">About Us</a></li>
          <li><a href="#" class="hover:text-yellow-300 transition">News & Event</a></li>
        </ul>
      </div>

      {{-- Lorem (opsional kolom tambahan) --}}
      <div>
        <h4 class="font-[500] text-[20px] mb-4">Lorem</h4>
        <ul class="space-y-2 text-white/90">
          <li><a href="#" class="hover:text-yellow-300 transition">Lorem ipsum</a></li>
          <li><a href="#" class="hover:text-yellow-300 transition">Lorem ipsum</a></li>
          <li><a href="#" class="hover:text-yellow-300 transition">Lorem ipsum</a></li>
        </ul>
      </div>

      {{-- Get In Touch --}}
      <div>
        <h4 class="font-[500] text-[20px] mb-4">Get In Touch</h4>
        <p class="text-white/90 mb-4">
          Email us, and we’ll reply ASAP. Stay tuned for updates and offers!
        </p>

        {{-- Form email --}}
        <form class="relative flex items-center max-w-full">
        {{-- Background kapsul --}}
        <div class="absolute inset-0 bg-[#3B8D73]/80 rounded-full"></div>

        {{-- Input --}}
        <input type="email"
                placeholder="Enter your email"
                class="relative flex-1 bg-transparent pl-5 pr-[90px] py-3.5 text-[16px] text-white placeholder-white/60 focus:outline-none focus:ring-0" />

        {{-- Tombol Send --}}
        <button type="submit"
                class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-full bg-[#F5C71A] px-6 py-1.5 text-[16px] font-[500] text-white hover:bg-[#f4d23d] transition">
            Send
        </button>
        </form>


      </div>
    </div>

    {{-- === Garis Pembatas === --}}
    <div class="mt-8 mb-3 border-t border-white/20"></div>

    {{-- === Bagian Bawah === --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
      <p class="text-white/80 text-[16px]">
        All Rights Reserved ©2023 Megah Abadi Kimia | Megah Abadi Kimia.
      </p>

      {{-- Sosial Media --}}
      <div class="flex items-center gap-3">
        <a href="#" class="flex h-[50px] w-[50px] text-[18px] items-center justify-center rounded-full bg-white/95 text-[#F5C71A] hover:-translate-y-0.5 transition"><i class="bi bi-linkedin"></i></a>
        <a href="#" class="flex h-[50px] w-[50px] text-[18px] items-center justify-center rounded-full bg-white/95 text-[#F5C71A] hover:-translate-y-0.5 transition"><i class="bi bi-instagram"></i></a>
        <a href="#" class="flex h-[50px] w-[50px] text-[18px] items-center justify-center rounded-full bg-white/95 text-[#F5C71A] hover:-translate-y-0.5 transition"><i class="bi bi-tiktok"></i></a>
      </div>
    </div>
  </div>


</footer>

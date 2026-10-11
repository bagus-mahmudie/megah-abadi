<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? config('app.name') }}</title>

  {{-- Bootstrap Icons CDN --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  {{-- Owl Carousel --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.theme.default.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js" defer></script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-white text-slate-800 antialiased font-sans">

  @include('partials.navbar')
  @if (session('success') || session('error'))
  <div class="container mt-4">
    <div class="rounded-xl px-4 py-3 {{ session('success') ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
      {{ session('success') ?? session('error') }}
    </div>
  </div>
@endif
  @include('partials.auth-modal')

  <main>
    {{ $slot ?? '' }}
    @yield('content')
  </main>

  @include('partials.footer')

  {{-- Scroll To Top Floating Button --}}
  <button id="scrollToTopBtn"
          onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
          class="fixed bottom-6 right-6 z-50 hidden h-11 w-11 items-center justify-center rounded-full bg-brand-green text-white shadow-lg hover:bg-brand-greenDark transition">
    <i class="bi bi-arrow-up text-lg"></i>
  </button>

{{-- modal verify agar tersedia di SEMUA halaman --}}
@include('partials.verify-email-modal')

{{-- Modal reset password --}}
@include('partials.reset-password-modal')

{{-- flag dari server -> JS akan membuka modal verify otomatis --}}
@if (session('show_verify'))
  <script>window.SHOW_VERIFY_MODAL = true;</script>
@endif

@auth
<script>
(function () {
  const badge = document.getElementById('navCartBadge');
  if (!badge) return;

  function updateBadge(count) {
    const num = parseInt(count || 0, 10) || 0;
    badge.textContent = num > 99 ? '99+' : num;
  }

  // Ambil jumlah item unik di keranjang saat halaman pertama dimuat
  document.addEventListener('DOMContentLoaded', function () {
    fetch("{{ route('cart.count') }}", { headers: { 'Accept': 'application/json' } })
      .then(r => r.ok ? r.json() : { count: 0 })
      .then(d => updateBadge(d.count))
      .catch(() => updateBadge(0));
  });
})();
</script>
@endauth

@push('scripts')
<script>
(function () {
  const badge = document.getElementById('navCartBadge');
  if (!badge) return;
  fetch('{{ route('cart.count') }}')
    .then(r=>r.json()).then(d=>{
      badge.textContent = (d && typeof d.count !== 'undefined') ? d.count : '0';
    }).catch(()=>{});
})();
</script>


</body>
</html>



@if ($products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->hasPages())
  @php
    $current = $products->currentPage();
    $last    = $products->lastPage();
    $window  = 2; // banyak tombol di sekitar halaman aktif

    $start = max(1, $current - $window);
    $end   = min($last, $current + $window);

    if ($current <= $window) $end = min($last, 1 + $window * 2);
    if ($current >= $last - $window) $start = max(1, $last - $window * 2);

    $rangeUrls = $products->getUrlRange($start, $end);
  @endphp

  <nav role="navigation" aria-label="Pagination" class="mt-10" id="ajaxPagination">
    <ul class="flex items-center justify-center gap-3">

      {{-- Previous --}}
      @if ($products->onFirstPage())
        <li aria-disabled="true" aria-label="@lang('pagination.previous')">
          <span class="w-10 h-10 flex items-center justify-center rounded-full bg-white text-slate-400 shadow-soft opacity-50">
            <i class="bi bi-arrow-left text-[18px]"></i>
          </span>
        </li>
      @else
        <li>
          <a href="{{ $products->previousPageUrl() }}" rel="prev"
             class="w-10 h-10 flex items-center justify-center rounded-full bg-white text-slate-700 shadow-soft hover:bg-slate-50"
             aria-label="@lang('pagination.previous')">
            <i class="bi bi-arrow-left text-[18px]"></i>
          </a>
        </li>
      @endif

      {{-- First + leading dots --}}
      @if ($start > 1)
        <li>
          <a href="{{ $products->url(1) }}"
             class="w-10 h-10 flex items-center justify-center rounded-full bg-white text-slate-700 shadow-soft hover:bg-slate-50">1</a>
        </li>
        @if ($start > 2)
          <li><span class="w-10 h-10 flex items-center justify-center rounded-full bg-white text-slate-400 shadow-soft">…</span></li>
        @endif
      @endif

      {{-- Numbered range --}}
      @foreach ($rangeUrls as $page => $url)
        @if ($page == $current)
          <li aria-current="page">
            <span class="w-10 h-10 flex items-center justify-center rounded-full bg-brand-green text-white shadow-soft font-medium">
              {{ $page }}
            </span>
          </li>
        @else
          <li>
            <a href="{{ $url }}"
               class="w-10 h-10 flex items-center justify-center rounded-full bg-white text-slate-700 shadow-soft hover:bg-slate-50"
               aria-label="@lang('pagination.goto_page', ['page' => $page])">
              {{ $page }}
            </a>
          </li>
        @endif
      @endforeach

      {{-- trailing dots + Last --}}
      @if ($end < $last)
        @if ($end < $last - 1)
          <li><span class="w-10 h-10 flex items-center justify-center rounded-full bg-white text-slate-400 shadow-soft">…</span></li>
        @endif
        <li>
          <a href="{{ $products->url($last) }}"
             class="w-10 h-10 flex items-center justify-center rounded-full bg-white text-slate-700 shadow-soft hover:bg-slate-50">{{ $last }}</a>
        </li>
      @endif

      {{-- Next --}}
      @if ($products->hasMorePages())
        <li>
          <a href="{{ $products->nextPageUrl() }}" rel="next"
             class="w-10 h-10 flex items-center justify-center rounded-full bg-white text-slate-700 shadow-soft hover:bg-slate-50"
             aria-label="@lang('pagination.next')">
            <i class="bi bi-arrow-right text-[18px]"></i>
          </a>
        </li>
      @else
        <li aria-disabled="true" aria-label="@lang('pagination.next')">
          <span class="w-10 h-10 flex items-center justify-center rounded-full bg-white text-slate-400 shadow-soft opacity-50">
            <i class="bi bi-arrow-right text-[18px]"></i>
          </span>
        </li>
      @endif

    </ul>
  </nav>
@endif

{{-- resources/views/partials/verify-email-modal.blade.php --}}
<div id="verifyModal" class="fixed inset-0 z-[90] hidden">
  <div id="verifyOverlay" class="absolute inset-0 bg-black/60"></div>

  <div class="absolute inset-x-0 top-0 flex min-h-full items-center justify-center p-4">
    <div id="verifyPanel" class="relative w-full max-w-[480px] rounded-2xl bg-white shadow-2xl p-7">
      <button id="closeVerifyModal" type="button"
        class="absolute right-3 top-3 py-1.5 px-3 inline-flex h-8 w-8 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100">
        ✕
      </button>

      <h2 class="text-center text-[22px] font-semibold text-slate-900">
        Verifikasi Email Kamu
      </h2>
      <p class="mt-2 text-center text-[15px] text-slate-600">
        Kami sudah mengirimkan link verifikasi ke alamat email kamu.
        Silahkan cek inbox email anda untuk menyelesaikan proses pendaftaran.
      </p>

      <div class="mt-6 flex justify-center">
        <button id="okVerify" type="button"
          class="rounded-xl bg-brand-green px-5 py-2.5 text-white font-medium hover:bg-emerald-700">
          Oke, Saya Mengerti
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function(){
  const modal  = document.getElementById('verifyModal');
  const overlay= document.getElementById('verifyOverlay');
  const closeX = document.getElementById('closeVerifyModal');
  const okBtn  = document.getElementById('okVerify');

  function open(){ modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
  function close(){ modal.classList.add('hidden');   document.body.classList.remove('overflow-hidden'); }

  overlay?.addEventListener('click', close);
  closeX?.addEventListener('click', close);
  okBtn?.addEventListener('click', close);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });

  if (window.SHOW_VERIFY_MODAL) open();  // flag dari session
})();
</script>
@endpush

{{-- Modal Login/Register --}}
<div id="authModal" class="fixed inset-0 z-[80] hidden">
  {{-- overlay --}}
  <div id="authOverlay" class="absolute inset-0 bg-black/40"></div>

  {{-- panel --}}
  <div class="absolute inset-x-0 top-0 flex min-h-full items-center justify-center p-4 sm:pt-20">
    <div id="authPanel" class="relative w-full max-w-[450px] rounded-2xl bg-white shadow-2xl">
      {{-- tombol close --}}
      <button id="closeAuthModal" class="absolute right-3 top-3 rounded-full py-1.5 px-3 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
        ✕
      </button>

      {{-- header --}}
      <div id="authHeader" class="px-6 pt-8">
        <h2 id="authTitle" class="text-center text-[24px] font-semibold text-slate-800">
            Daftar Sekarang
        </h2>
        <p id="authSubtitle" class="mt-1 text-center text-[16px] text-slate-500">
            Sudah punya akun Megah Abadi?
            <button id="switchToLogin" class="text-brand-green font-medium underline">Masuk</button>
        </p>
      </div>

      {{-- tab --}}
      <div id="authTabs" class="mt-4 px-6">
        <div class="grid grid-cols-2 gap-2 rounded-[14px] bg-slate-100 p-1.5 text-sm">
          <button id="tabDaftar" class="tab-auth active rounded-[12px] bg-white py-2 font-medium shadow text-[16px]">Daftar</button>
          <button id="tabMasuk"  class="tab-auth rounded-[12px] py-2 font-medium text-slate-500 text-[16px]">Masuk</button>
        </div>
      </div>

      
      {{-- form daftar --}}
      <form id="formDaftar" method="POST" action="{{ route('register') }}" class="px-6 pt-4 pb-2">
        @csrf
        {{-- ALERT khusus register --}}
        @if ($errors->register->has('reg_email') || $errors->register->has('reg_password'))
          <div class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">
            {{ $errors->register->first('reg_email') ?: $errors->register->first('reg_password') }}
          </div>
        @endif
        <input type="hidden" name="return_to" value="{{ url()->current() }}">
        <label class="block text-xs text-slate-500 mb-1">Email</label>
        <input type="email" name="reg_email" value="{{ old('reg_email') }}"
        class="w-full rounded-xl border px-3 py-3 text-[14px] outline-none focus:ring-2
        @error('reg_email')  focus:ring-red-200 @else border-slate-200 focus:ring-brand-green/30 @enderror"
        placeholder="Masukkan Email" />
               {{-- <p class="mt-0 text-[13px] text-slate-400">Contoh : megahabadi@gmail.com</p> --}}
        <div class="mt-3">
          <label class="block text-xs text-slate-500 mb-1">Password</label>
          {{-- <input type="password" name="reg_password" placeholder="Masukkan Password" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-[14px] outline-none focus:ring-2 focus:ring-brand-green/30" /> --}}
          <div class="relative">
            <input id="registerPassword" type="password" name="reg_password"
                  class="w-full rounded-xl border px-3 py-3 pr-10 text-[14px] outline-none focus:ring-2
                  @error('reg_password','register') focus:ring-red-200 @else border-slate-200 focus:ring-brand-green/30 @enderror"
                  placeholder="Masukkan Password" />
            <button type="button" id="toggleRegisterEye"
                    class="absolute inset-y-0 right-2 my-auto h-8 w-8 rounded-full text-slate-500 hover:bg-slate-100 flex items-center justify-center"
                    aria-label="Toggle password">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>
        <button type="submit" class="mt-4 w-full rounded-xl bg-brand-green py-3 text-white font-medium">
          Daftar <i class="bi bi-arrow-right-short"></i>
        </button>

        <div class="flex items-center gap-4 my-4">
            <div class="flex-1 h-px bg-slate-300"></div>
            <span class=" text-[16px] text-slate-500 font-medium">atau</span>
            <div class="flex-1 h-px bg-slate-300"></div>
        </div>

        <a href="{{ route('oauth.google.redirect') }}"
           class="mb-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 py-3">
          <img alt="google" src="https://www.google.com/favicon.ico" class="h-5 w-5">
          <span class="text-[16px] font-medium">Daftar dengan Google</span>
        </a>
      </form>

      
      {{-- form masuk --}}
      <form id="formMasuk" method="POST" action="{{ route('login') }}" class="px-6 pt-4 pb-6 hidden">
        @csrf
        <input type="hidden" name="return_to" value="{{ url()->current() }}">
        {{-- ALERT khusus login --}}
        @if (session('login_error') || $errors->login->has('login_email') || $errors->login->has('login_password'))
          <div class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">
            {{ session('login_error') ?: ($errors->login->first('login_email') ?: $errors->login->first('login_password')) }}
          </div>
        @endif
        <label class="block text-xs text-slate-500 mb-1">Email</label>
        {{-- <input type="email" name="login_email" placeholder="Masukkan Nomor HP atau Email" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-[14px] outline-none focus:ring-2 focus:ring-brand-green/30" /> --}}
        <input type="email" name="login_email"
        class="w-full rounded-xl border px-3 py-3 text-[14px] outline-none focus:ring-2
        @error('login_email')  focus:ring-red-200 @else border-slate-200 focus:ring-brand-green/30 @enderror"
        placeholder="Masukkan Email" />
        <div class="mt-3">
          <label class="block text-xs text-slate-500 mb-1">Password</label>
          {{-- <input type="password" name="login_password" placeholder="Masukkan Password" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-[14px] outline-none focus:ring-2 focus:ring-brand-green/30" /> --}}
          <div class="relative">
            <input id="loginPassword" type="password" name="login_password"
                  class="w-full rounded-xl border px-3 py-3 pr-10 text-[14px] outline-none focus:ring-2
                  @error('login_password','login')  focus:ring-red-200 @else border-slate-200 focus:ring-brand-green/30 @enderror"
                  placeholder="Masukkan Password" />
            <button type="button" id="toggleLoginEye"
                    class="absolute inset-y-0 right-2 my-auto h-8 w-8 rounded-full text-slate-500 hover:bg-slate-100 flex items-center justify-center"
                    aria-label="Toggle password">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <div class="mt-3 flex items-center justify-between">
          <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-green focus:ring-brand-green/30"
                  {{ old('remember') ? 'checked' : '' }}>
            Ingat saya
          </label>

          <button type="button" id="linkForgot" class="text-sm text-brand-green hover:underline">
            Forget Password?
          </button>
        </div>

        <button type="submit" class="mt-4 w-full rounded-xl bg-brand-green py-3 text-white font-medium">
          Masuk
        </button>

        <div class="flex items-center gap-4 my-4">
            <div class="flex-1 h-px bg-slate-300"></div>
            <span class=" text-[16px] text-slate-500 font-medium">atau</span>
            <div class="flex-1 h-px bg-slate-300"></div>
        </div>

        <a href="{{ route('oauth.google.redirect', ['return_to' => url()->current()]) }}"
           class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 py-3">
          <img alt="google" src="https://www.google.com/favicon.ico" class="h-5 w-5">
          <span class="text-[16px] font-medium">Masuk dengan Google</span>
        </a>

        
      </form>

      {{-- FORM LUPA PASSWORD (hidden default) --}}
      <form id="formForgot" method="POST" action="{{ route('password.email') }}" class="px-6 pt-6 pb-6 hidden">
        @csrf

        <div class="text-center mb-4">
          <h3 class="text-[24px] font-semibold text-slate-800">Lupa Password</h3>
          <p class="mt-2 text-[16px] text-slate-500">
            Masukkan email terdaftar kamu untuk menerima tautan reset password.
          </p>
        </div>

        {{-- Status sukses/throttle --}}
        @if (session('forgot_status'))
          <div class="mb-3 rounded-lg bg-green-50 px-3 py-2 text-sm text-green-700">
            {{ session('forgot_status') }}
          </div>
        @endif

        <label class="block text-xs text-slate-500 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email') }}"
              class="w-full rounded-xl border px-3 py-3 text-[14px] outline-none focus:ring-2
              @error('email')  focus:ring-red-200 @else border-slate-200 focus:ring-brand-green/30 @enderror"
              placeholder="Masukkan Email" />
        <p class="mt-1 text-[13px] text-slate-400">Contoh: megahabadi@gmail.com</p>

        <button type="submit" class="mt-4 w-full rounded-xl bg-brand-green py-3 text-white font-medium">
          Kirim
        </button>

        <div class="mt-4 text-center">
          <button type="button" id="backToLogin" class="text-sm text-brand-green hover:underline">
            Kembali
          </button>
        </div>
      </form>


    </div>
  </div>
</div>

{{-- Flag untuk auto-buka modal dan memilih tab --}}
@if (session('auth_tab') === 'register' || $errors->has('reg_email') || $errors->has('reg_password'))
  <script>window.SHOW_AUTH_MODAL = 'register';</script>
@endif

@if (session('auth_tab') === 'login' || session('login_error') || $errors->has('login_email') || $errors->has('login_password'))
  <script>window.SHOW_AUTH_MODAL = 'login';</script>
@endif

@push('scripts')
<script>
(function () {
  const $ = (s, el=document)=>el.querySelector(s);

  const modal   = $('#authModal');
  const overlay = $('#authOverlay');
  const panel   = $('#authPanel');
  const closeBtn= $('#closeAuthModal');

  // elemen header (BARU)
  const titleEl = $('#authTitle');
  const subEl   = $('#authSubtitle');

  // === buka & tutup modal
  function openModal() {
    if (!modal) return;
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }
  function closeModal() {
    if (!modal) return;
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }

  // === event dasar modal
  overlay?.addEventListener('click', closeModal);
  closeBtn?.addEventListener('click', closeModal);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  // === tombol pembuka dari topbar (open + default ke "daftar")
  ['#btnTopbarSignup', '#linkTopbarSignup'].forEach(sel => {
    const btn = document.querySelector(sel);
    if (btn) btn.addEventListener('click', (e) => { e.preventDefault(); openModal(); showPanel('daftar'); });
  });

  // === refs form & tab
  const tabDaftar  = $('#tabDaftar');
  const tabMasuk   = $('#tabMasuk');
  const formDaftar = $('#formDaftar');
  const formMasuk  = $('#formMasuk');
  const formForgot = $('#formForgot'); // tambahan
  const authHeader = $('#authHeader');
  const authTabs   = $('#authTabs');

  // helper: set style tab
  function setTabs(active) {
    const tabDaftar = $('#tabDaftar');
    const tabMasuk  = $('#tabMasuk');
    if (!tabDaftar || !tabMasuk) return;
    const activeCls   = ['bg-white','shadow','text-slate-800'];
    const inactiveCls = ['text-slate-500'];

    tabDaftar.classList.remove(...activeCls, ...inactiveCls);
    tabMasuk .classList.remove(...activeCls, ...inactiveCls);

    if (active === 'daftar') {
      tabDaftar.classList.add(...activeCls);
      tabMasuk .classList.add(...inactiveCls);
    } else if (active === 'masuk') {
      tabMasuk .classList.add(...activeCls);
      tabDaftar.classList.add(...inactiveCls);
    } else {
      tabDaftar.classList.add(...inactiveCls);
      tabMasuk .classList.add(...inactiveCls);
    }
  }

  // BARU: ganti judul & subtitle header sesuai panel
  function setHeaderFor(name) {
    if (!titleEl || !subEl) return;

    if (name === 'masuk') {
      titleEl.textContent = 'Masuk Sekarang';
      subEl.innerHTML = `Belum punya akun Megah Abadi?
        <button id="switchToRegister" class="text-brand-green font-medium underline">Daftar</button>`;
    } else if (name === 'daftar') {
      titleEl.textContent = 'Daftar Sekarang';
      subEl.innerHTML = `Sudah punya akun Megah Abadi?
        <button id="switchToLogin" class="text-brand-green font-medium underline">Masuk</button>`;
    } else { // forgot
      titleEl.textContent = 'Lupa Password';
      subEl.innerHTML = '';
    }

    // re-bind link switch yang baru disisipkan
    $('#switchToLogin')   ?.addEventListener('click', ()=>showPanel('masuk'));
    $('#switchToRegister')?.addEventListener('click', ()=>showPanel('daftar'));
  }

  // tampilkan panel tertentu
  function showPanel(name) {
    formDaftar?.classList.add('hidden');
    formMasuk ?.classList.add('hidden');
    formForgot?.classList.add('hidden');

    if (name === 'forgot') {
      formForgot?.classList.remove('hidden');
      authHeader?.classList.remove('hidden'); // tetap tampilkan header
      authTabs  ?.classList.add('hidden');
      setTabs('none');
      setHeaderFor('forgot'); // BARU
    } else if (name === 'daftar') {
      formDaftar?.classList.remove('hidden');
      authHeader?.classList.remove('hidden');
      authTabs  ?.classList.remove('hidden');
      setTabs('daftar');
      setHeaderFor('daftar'); // BARU
    } else { // 'masuk'
      formMasuk?.classList.remove('hidden');
      authHeader?.classList.remove('hidden');
      authTabs  ?.classList.remove('hidden');
      setTabs('masuk');
      setHeaderFor('masuk'); // BARU
    }
  }

  // kompatibel fungsi lama
  function activate(tab) { showPanel(tab === 'daftar' ? 'daftar' : 'masuk'); }

  // bind tab
  tabDaftar?.addEventListener('click', ()=>showPanel('daftar'));
  tabMasuk ?.addEventListener('click', ()=>showPanel('masuk'));

  // eye toggle di password login
  const eyeBtn = $('#toggleLoginEye');
  const pwdInp = $('#loginPassword');
  if (eyeBtn && pwdInp) {
    eyeBtn.addEventListener('click', () => {
      const isPwd = pwdInp.getAttribute('type') === 'password';
      pwdInp.setAttribute('type', isPwd ? 'text' : 'password');
      eyeBtn.innerHTML = isPwd ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });
  }

  // eye toggle REGISTER
  const regEyeBtn = $('#toggleRegisterEye');
  const regPwdInp = $('#registerPassword');
  if (regEyeBtn && regPwdInp) {
    regEyeBtn.addEventListener('click', () => {
      const isPwd = regPwdInp.getAttribute('type') === 'password';
      regPwdInp.setAttribute('type', isPwd ? 'text' : 'password');
      regEyeBtn.innerHTML = isPwd ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });
  }

  // eye toggle RESET
  const resetEyeBtn = $('#toggleResetEye');
  const resetPwdInp = $('#resetPassword');
  if (resetEyeBtn && resetPwdInp) {
    resetEyeBtn.addEventListener('click', () => {
      const isPwd = resetPwdInp.getAttribute('type') === 'password';
      resetPwdInp.setAttribute('type', isPwd ? 'text' : 'password');
      resetEyeBtn.innerHTML = isPwd ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });
  }
  const resetConfirmEyeBtn = $('#toggleResetConfirmEye');
  const resetConfirmPwdInp = $('#resetPasswordConfirm');
  if (resetConfirmEyeBtn && resetConfirmPwdInp) {
    resetConfirmEyeBtn.addEventListener('click', () => {
      const isPwd = resetConfirmPwdInp.getAttribute('type') === 'password';
      resetConfirmPwdInp.setAttribute('type', isPwd ? 'text' : 'password');
      resetConfirmEyeBtn.innerHTML = isPwd ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });
  }

  // forgot password: link & back
  $('#linkForgot') ?.addEventListener('click', ()=> showPanel('forgot'));
  $('#backToLogin')?.addEventListener('click', ()=> showPanel('masuk'));

  // auto-open dari server (flash flag)
  if (window.SHOW_AUTH_MODAL === 'register') { openModal(); showPanel('daftar'); }
  if (window.SHOW_AUTH_MODAL === 'login')    { openModal(); showPanel('masuk');  }
  if (window.SHOW_AUTH_MODAL === 'forgot')   { openModal(); showPanel('forgot'); }

  // expose API global & binding tombol eksternal
  window.openAuthModal = function(tab = 'masuk') {
    openModal();
    showPanel(tab === 'daftar' ? 'daftar' : (tab === 'forgot' ? 'forgot' : 'masuk'));
  };

  document.querySelectorAll('[data-auth-open]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const want = (btn.dataset.authOpen || 'login').toLowerCase();
      // map: login→masuk, register→daftar, forgot→forgot
      const tab = (want === 'register') ? 'daftar' : (want === 'forgot' ? 'forgot' : 'masuk');
      window.openAuthModal(tab);
    });
  });

})();
</script>
@endpush


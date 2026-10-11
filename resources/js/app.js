  // Import Vite HMR (default)
  import './bootstrap';

  const qs = (s, el = document) => el.querySelector(s);
  const qsa = (s, el = document) => [...el.querySelectorAll(s)];

  // Mobile menu toggle
  const menuBtn = qs('#menuBtn');
  const mobileMenu = qs('#mobileMenu');
  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });
  }

  // Dropdowns (kategori & bahasa)
  qsa('[data-dropdown-btn]').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = qs(btn.dataset.dropdownBtn);
      if (target) target.classList.toggle('hidden');
    });
  });

  const scrollBtn = document.getElementById('scrollToTopBtn');
    window.addEventListener('scroll', () => {
      if (window.scrollY > 300) {
        scrollBtn.classList.remove('hidden');
      } else {
        scrollBtn.classList.add('hidden');
      }
  });

  (function(){
  const topBar  = document.getElementById('topBar');
  const mainNav = document.getElementById('mainNav');
  const root    = document.documentElement;
  const DUR = 300; // ms (harus sama dengan duration CSS)

  function setTopbarHeightVar() {
    root.style.setProperty('--topbar-h', (topBar.offsetHeight || 0) + 'px');
  }
  function setOffsetVisible(){ // navbar di bawah topbar
    root.style.setProperty('--nav-offset', (topBar.offsetHeight || 0) + 'px');
    mainNav.classList.remove('nav-stuck');
  }
  function setOffsetHidden(){  // navbar di top-0
    root.style.setProperty('--nav-offset', '0px');
    mainNav.classList.add('nav-stuck');
  }

  // INIT
  setTopbarHeightVar();
  if (window.scrollY > 10) {
    topBar.classList.add('is-hidden');
    setOffsetHidden();
  } else {
    setOffsetVisible();
  }

  window.addEventListener('resize', () => {
    setTopbarHeightVar();
    if (!topBar.classList.contains('is-hidden')) setOffsetVisible();
  }, { passive:true });

  let lastY = window.scrollY;
  const THRESH = 5;
  let animating = false;

  function hideTopbar() {
    if (animating || topBar.classList.contains('is-hidden')) return;
    animating = true;
    setTopbarHeightVar();

    // 1) Mulai slide: topbar naik, navbar ikut naik dengan transform (menempel)
    topBar.classList.add('is-hidden');
    mainNav.classList.add('nav-slide-up');

    // 2) Setelah animasi selesai: kunci posisi navbar ke top:0, reset transform
    setTimeout(() => {
      mainNav.classList.remove('nav-slide-up');
      setOffsetHidden(); // top:0
      animating = false;
    }, DUR);
  }

  function showTopbar() {
    if (animating || !topBar.classList.contains('is-hidden')) return;
    animating = true;
    setTopbarHeightVar();

    // 1) Siapkan: saat ini navbar ada di top:0; geser dulu kebawah sebesar tinggi topbar (transform)
    mainNav.classList.add('nav-slide-down');

    // 2) Mulai turunkan topbar bersamaan
    requestAnimationFrame(() => {
      topBar.classList.remove('is-hidden');
    });

    // 3) Di akhir animasi: kunci offset navbar di bawah topbar, reset transform
    setTimeout(() => {
      setOffsetVisible(); // top: var(--topbar-h)
      mainNav.classList.remove('nav-slide-down');
      animating = false;
    }, DUR);
  }

  window.addEventListener('scroll', () => {
    const y = window.scrollY;
    const dy = y - lastY;

    if (dy > THRESH && y > 10) hideTopbar();   // scroll turun -> sembunyikan
    if (dy < -THRESH)           showTopbar();  // scroll naik  -> tampilkan
    if (y <= 10)                showTopbar();  // di puncak -> pastikan tampil

    lastY = y;
  }, { passive:true });
})();

(function () {
  const $ = (s, el = document) => el.querySelector(s);

  const modal = $('#authModal');
  const overlay = $('#authOverlay');
  const panel = $('#authPanel');
  const closeBtn = $('#closeAuthModal');

  const tabDaftar = $('#tabDaftar');
  const tabMasuk  = $('#tabMasuk');
  const formDaftar = $('#formDaftar');
  const formMasuk  = $('#formMasuk');

  const titleEl = $('#authTitle');      // <h2 id="authTitle">...</h2>
  const subtitleEl = $('#authSubtitle'); // <p id="authSubtitle">...</p>

  // ================== Core ==================
  function openModal(mode = 'daftar') {
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    activate(mode);
    // fokus ke input pertama di form aktif
    const firstInput = (mode === 'daftar' ? formDaftar : formMasuk)?.querySelector('input');
    if (firstInput) setTimeout(() => firstInput.focus(), 50);
  }

  function closeModal() {
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }

  // ================== Tabs / Header ==================
  function activate(tab) {
    const isDaftar = tab === 'daftar';

    // tampil/hidden form
    formDaftar.classList.toggle('hidden', !isDaftar);
    formMasuk.classList.toggle('hidden', isDaftar);

    // style tab
    tabDaftar.classList.toggle('bg-white', isDaftar);
    tabDaftar.classList.toggle('shadow', isDaftar);
    tabDaftar.classList.toggle('text-slate-800', isDaftar);
    tabDaftar.classList.toggle('text-slate-500', !isDaftar);

    tabMasuk.classList.toggle('bg-white', !isDaftar);
    tabMasuk.classList.toggle('shadow', !isDaftar);
    tabMasuk.classList.toggle('text-slate-800', !isDaftar);
    tabMasuk.classList.toggle('text-slate-500', isDaftar);

    // header & subtitle dinamis
    if (isDaftar) {
      titleEl.textContent = 'Daftar Sekarang';
      subtitleEl.innerHTML = `Sudah punya akun Megah Abadi?
        <button id="switchToLogin" class="text-brand-green font-medium underline">Masuk</button>`;
    } else {
      titleEl.textContent = 'Masuk Sekarang';
      subtitleEl.innerHTML = `Belum punya akun Megah Abadi?
        <button id="switchToRegister" class="text-brand-green font-medium underline">Daftar</button>`;
    }

    // re-bind tombol di subtitle yang baru di-inject
    $('#switchToLogin')?.addEventListener('click', () => activate('masuk'));
    $('#switchToRegister')?.addEventListener('click', () => activate('daftar'));
  }

  // ================== Close behaviors ==================
  // klik overlay
  overlay?.addEventListener('click', closeModal);

  // klik area kosong di kontainer modal (bukan di panel)
  modal.addEventListener('click', (e) => {
    if (!panel.contains(e.target)) closeModal();
  });
  // cegah bubbling dari dalam panel
  panel.addEventListener('click', (e) => e.stopPropagation());

  // tombol X
  closeBtn?.addEventListener('click', closeModal);

  // ESC
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  // ================== Open triggers ==================
  // contoh: dari topbar kuning “Sign up”
  ['#btnTopbarSignup', '#linkTopbarSignup'].forEach((sel) => {
    const el = document.querySelector(sel);
    if (el) el.addEventListener('click', (e) => { e.preventDefault(); openModal('daftar'); });
  });

  // opsional: kalau kamu punya tombol lain untuk langsung buka tab "masuk"
  // beri attribute data-auth-mode="masuk" pada elemen trigger
  document.querySelectorAll('[data-auth-mode]').forEach((el) => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      openModal(el.getAttribute('data-auth-mode') === 'masuk' ? 'masuk' : 'daftar');
    });
  });

  // ================== Tab listeners ==================
  tabDaftar?.addEventListener('click', () => activate('daftar'));
  tabMasuk ?.addEventListener('click', () => activate('masuk'));

  // jika ingin default saat halaman load ke "daftar"
  // activate('daftar');
})();

(function(){
  const m = document.getElementById('verifyModal');
  const o = document.getElementById('verifyOverlay');
  const x = document.getElementById('closeVerifyModal');
  function open(){ m.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
  function close(){ m.classList.add('hidden');   document.body.classList.remove('overflow-hidden'); }
  o?.addEventListener('click', close);
  x?.addEventListener('click', close);
  document.addEventListener('keydown', e=>{ if(e.key==='Escape') close(); });

  if (window.SHOW_VERIFY_MODAL) open();
})();

(function () {
  // Avatar dropdown
  const wrap = document.getElementById('profileMenu');
  const btn  = document.getElementById('btnProfile');
  const menu = document.getElementById('menuProfile');

  if (wrap && btn && menu) {
    const close = () => menu.classList.add('hidden');
    const toggle = () => menu.classList.toggle('hidden');

    btn.addEventListener('click', e => { e.stopPropagation(); toggle(); });
    document.addEventListener('click', e => { if (!wrap.contains(e.target)) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
  }

  // (opsional) kalau kamu sudah punya JS untuk open modal auth via #btnTopbarSignup / #linkTopbarSignup,
  // bagian ini boleh di-skip. Dibiarkan untuk berjaga-jaga:
  const openAuth = () => {
    const m = document.getElementById('authModal');
    if (m) { m.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
  };
  ['btnTopbarSignup','linkTopbarSignup'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('click', e => { e.preventDefault(); openAuth(); });
  });
})();

(function () {
  // ===== Dropdown guest (ikon bi-person) =====
  const gWrap = document.getElementById('guestMenuWrap');
  const gBtn  = document.getElementById('guestBtn');
  const gMenu = document.getElementById('guestMenu');

  if (gWrap && gBtn && gMenu) {
    const gClose  = () => gMenu.classList.add('hidden');
    const gToggle = () => gMenu.classList.toggle('hidden');

    gBtn.addEventListener('click', (e)=>{ e.stopPropagation(); gToggle(); });
    document.addEventListener('click', (e)=>{ if (!gWrap.contains(e.target)) gClose(); });
    document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') gClose(); });
  }

  // ===== Utilities untuk modal auth =====
  function openAuth() {
    const m = document.getElementById('authModal');
    if (m) { m.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
  }
  function switchAuthTab(mode) {
    // klik tombol tab agar semua handler/teks header ikut jalan
    const tabId = (mode === 'daftar') ? 'tabDaftar' : 'tabMasuk';
    setTimeout(()=> document.getElementById(tabId)?.click(), 0);
  }
  function openAnd(mode) { openAuth(); switchAuthTab(mode); }

  // Topbar "Sign up" → buka modal tab Daftar
  const topSignup = document.getElementById('linkTopbarSignup');
  topSignup?.addEventListener('click', (e)=>{ e.preventDefault(); openAnd('daftar'); });

  // Ikon user di kanan (guest) → menu Daftar/Login
  document.getElementById('guestOpenDaftar')
    ?.addEventListener('click', ()=> { openAnd('daftar'); gMenu?.classList.add('hidden'); });

  document.getElementById('guestOpenLogin')
    ?.addEventListener('click', ()=> { openAnd('masuk');  gMenu?.classList.add('hidden'); });

  // (opsional) tombol bi-person lain yg kamu pakai sebelumnya
  const btnTopbarSignup = document.getElementById('btnTopbarSignup');
  btnTopbarSignup?.addEventListener('click', (e)=>{ e.preventDefault(); openAnd('daftar'); });
})();


(() => {
  const form = document.getElementById('reviewForm');
  const isReadOnly = form?.dataset.readonly === '1';

  // Kalau readonly, hentikan interaksi rating & submit
  if (isReadOnly) {
    // matikan handler klik bintang
    const wrap = document.getElementById('ratingStars');
    if (wrap) {
      wrap.querySelectorAll('.star').forEach(btn => {
        btn.addEventListener('click', e => e.preventDefault(), { passive: false });
      });
    }
    // cegah submit (fallback selain onsubmit=false)
    form?.addEventListener('submit', e => e.preventDefault());
  } else {
    // ====== logika rating normal (jika belum pernah review) ======
    const wrap  = document.getElementById('ratingStars');
    const input = document.getElementById('ratingInput');
    if (wrap && input) {
      const paint = n => wrap.querySelectorAll('.star i').forEach((icon, idx)=>{
        icon.className = (idx < n) ? 'bi bi-star-fill' : 'bi bi-star';
      });
      wrap.addEventListener('click', e=>{
        const btn = e.target.closest('.star'); if(!btn) return;
        const val = parseInt(btn.dataset.star,10) || 0;
        input.value = val; paint(val);
      });
      // inisialisasi
      paint(parseInt(input.value || '0', 10));
    }
  }
})();


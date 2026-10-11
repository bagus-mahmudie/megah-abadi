@if (request()->routeIs('password.reset'))
  <div id="resetModal"
       class="fixed inset-0 z-[90] flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="relative w-full max-w-[420px] rounded-2xl bg-white shadow-2xl p-6">
      {{-- tombol close --}}
      <button onclick="window.location='{{ url()->previous() }}'"
              class="absolute right-3 top-3 rounded-full py-1.5 px-3 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
        ✕
      </button>

      {{-- header --}}
      <div class="text-center mb-4">
        <h2 class="text-[24px] font-semibold text-slate-800">Reset Password</h2>
        <p class="mt-1 text-[16px] text-slate-500">
          Masukkan password baru untuk akun kamu.
        </p>
      </div>

      {{-- FORM RESET PASSWORD --}}
      <form method="POST" action="{{ route('password.update') }}" class="space-y-3">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ request('email') }}">

        {{-- Password baru + eye toggle --}}
        <div class="relative">
          <input id="resetPassword" type="password" name="password"
                 placeholder="Kata sandi baru"
                 class="w-full rounded-xl border px-3 py-3 pr-10 text-[14px] outline-none focus:ring-2 border-slate-200 focus:ring-brand-green/30"
                 required>
          <button type="button" id="toggleResetEye"
                  class="absolute inset-y-0 right-2 my-auto h-8 w-8 rounded-full text-slate-500 hover:bg-slate-100 flex items-center justify-center"
                  aria-label="Toggle password">
            <i class="bi bi-eye"></i>
          </button>
        </div>

        {{-- Konfirmasi password + eye toggle --}}
        <div class="relative">
          <input id="resetPasswordConfirm" type="password" name="password_confirmation"
                 placeholder="Konfirmasi sandi baru"
                 class="w-full rounded-xl border px-3 py-3 pr-10 text-[14px] outline-none focus:ring-2 border-slate-200 focus:ring-brand-green/30"
                 required>
          <button type="button" id="toggleResetConfirmEye"
                  class="absolute inset-y-0 right-2 my-auto h-8 w-8 rounded-full text-slate-500 hover:bg-slate-100 flex items-center justify-center"
                  aria-label="Toggle password">
            <i class="bi bi-eye"></i>
          </button>
        </div>

        <button type="submit"
                class="mt-3 w-full rounded-xl bg-brand-green py-3 text-white font-medium">
          Simpan Password Baru
        </button>
      </form>
    </div>
  </div>
@endif

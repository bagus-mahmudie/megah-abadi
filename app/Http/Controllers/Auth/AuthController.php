<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Support\Str;   
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;
use App\Models\PendingRegistration;
use App\Mail\VerifyPendingRegistrationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    private function ttlMinutes(): int
    {
        return (int) config('auth_pending.ttl_minutes', 60);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login_email'    => 'required|email',
            'login_password' => 'required',
        ], [], [
            'login_email'    => 'email',
            'login_password' => 'password',
        ]);

        if ($validator->fails()) {
        return back()
            ->withErrors($validator, 'login') // <-- error bag khusus login
            ->withInput()
            ->with('auth_tab', 'login');
        }

        $data = $validator->validated();

        $remember = $request->boolean('remember');

        $returnTo = $request->input('return_to') ?: url()->previous() ?: route('home');

        if (Auth::attempt(['email' => $data['login_email'], 'password' => $data['login_password']], $remember)) {
            $request->session()->regenerate();
            // Arahkan kembali ke halaman asal
            return redirect()->to($returnTo)->with('success', 'Selamat datang kembali!');
        }

        // << tambah ini
        return back()
            ->with('login_error', 'Email atau password salah.')
            ->with('auth_tab', 'login');
    }

    // ===================== REGISTER (PENDING) =====================
    public function register(Request $request)
    {
        $ttl    = $this->ttlMinutes();
        $cutoff = now()->subMinutes($ttl);

        $rules = [
            'reg_email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
                Rule::unique('pending_registrations', 'email')->where(fn($q) => $q->where('created_at', '>', $cutoff)),
            ],
            'reg_password' => 'required|min:6',
            'reg_name' => 'nullable|string|max:255',
        ];

        $messages = [
            'reg_email.required' => 'Email wajib diisi.',
            'reg_email.email'    => 'Format email tidak valid.',
            'reg_email.unique'   => 'Email sudah terdaftar. Silahkan gunakan email lain atau login.',
            'reg_password.required' => 'Password wajib diisi.',
            'reg_password.min'      => 'Password minimal 6 karakter.',
        ];

        $attributes = [
            'reg_email'    => 'Email',
            'reg_password' => 'Password',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'register')
                ->withInput()
                ->with('auth_tab', 'register'); // arahkan tab daftar
        }

        $data = $validator->validated();
        // bersihkan pending expired utk email ini (agar tidak menahan pendaftaran ulang)
        PendingRegistration::where('email', $data['reg_email'])
            ->where('created_at', '<=', $cutoff)
            ->delete();

        // cek pending aktif
        $pending = PendingRegistration::where('email', $data['reg_email'])
            ->where('created_at', '>', $cutoff)
            ->first();

        $autoName = $request->input('reg_name')
            ?: Str::before($data['reg_email'], '@')
            ?: 'Pengguna-'.Str::random(6);

        $cutoff = now()->subMinutes($this->ttlMinutes());
        PendingRegistration::where('email', $data['reg_email'])
            ->where('created_at', '<=', $cutoff)
            ->delete();

        if (!$pending) {
            $pending = PendingRegistration::create([
                'name'     => $autoName,
                'email'    => $data['reg_email'],
                'password' => Hash::make($data['reg_password']),
                'token'    => Str::random(40),
            ]);
        } else {
            $pending->update([
                'name'       => $autoName,
                'token'      => Str::random(40),
                'updated_at' => now(),
                'created_at' => now(), // reset TTL
            ]);
        }

        $signedUrl = URL::temporarySignedRoute(
            'register.confirm',
            now()->addMinutes($ttl),
            ['id' => $pending->id, 'token' => $pending->token]
        );

        Mail::to($pending->email)->send(new VerifyPendingRegistrationMail($pending, $signedUrl));

        $back = $request->input('return_to') ?: url()->previous() ?: route('home');
        return redirect()->to($back)->with([
            'show_verify' => true,
            'status'      => 'verification-link-sent',
        ]);
    }

    // ============== KONFIRMASI PENDING -> CREATE USER ==============
    public function confirmPending(Request $request, $id)
    {
        $ttl     = $this->ttlMinutes();
        $cutoff  = now()->subMinutes($ttl);

        $pending = PendingRegistration::findOrFail($id);

        if ($request->query('token') !== $pending->token) {
            abort(403, 'Token tidak valid.');
        }

        if ($pending->created_at->lte($cutoff)) {
            $pending->delete();
            return redirect()->route('home')->with('error', 'Link verifikasi kedaluwarsa. Silakan daftar ulang.');
        }

        if (User::where('email', $pending->email)->exists()) {
            $pending->delete();
            return redirect()->route('home')->with('success', 'Akun sudah aktif. Silakan masuk.');
        }

        $user = User::create([
            'name'              => $pending->name,
            'email'             => $pending->email,
            'password'          => $pending->password,
            'email_verified_at' => now(),
        ]);

        $pending->delete();

        Auth::login($user, true);

        return redirect()->route('home')->with('success', 'Email terverifikasi. Akun kamu sudah aktif!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return back();
    }
}

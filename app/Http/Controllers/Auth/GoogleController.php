<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirect(Request $request) // <-- terima Request
    {
        // simpan halaman asal ke session
        $returnTo = $request->input('return_to', url()->previous() ?: url()->current());
        session(['oauth_return_to' => $returnTo]);

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            // kalau gagal, balik ke halaman asal (kalau ada)
            $fallback = session('oauth_return_to') ?: route('home');
            return redirect()->to($fallback)->with('error','Gagal login dengan Google.');
        }

        // Ambil URL avatar Google + paksa ukuran lebih tinggi
        $rawAvatar = $googleUser->getAvatar(); // biasanya sudah https://lh3.googleusercontent.com/...
        // tambahkan ukuran default jika belum ada parameter ukuran
        if ($rawAvatar && !str_contains($rawAvatar, 'sz=')) {
            $separator = str_contains($rawAvatar, '?') ? '&' : '?';
            $rawAvatar = $rawAvatar . $separator . 'sz=256'; // NEW: high-res
        }

        $user = User::where('google_id', $googleUser->getId())
                    ->orWhere('email', $googleUser->getEmail())
                    ->first();

        if (!$user) {
            // User baru dari Google
            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'avatar'            => $rawAvatar,                 // ensure tersimpan
                'password'          => Hash::make(Str::random(32)),
                'email_verified_at' => Carbon::now(),
            ]);

            Password::sendResetLink(['email' => $user->email]);
        } else {
            // Tautkan google_id jika belum
            if (!$user->google_id) {
                $user->google_id = $googleUser->getId();
            }

            // NEW: segarkan avatar jika:
            // - avatar masih kosong, atau
            // - avatar lama adalah avatar Google juga (URL eksternal googleusercontent),
            //   supaya mengikuti foto terbaru user di Google.
            $shouldRefreshAvatar =
                empty($user->avatar) ||
                (Str::startsWith($user->avatar, ['http://', 'https://']) &&
                str_contains($user->avatar, 'googleusercontent.com'));

            if ($shouldRefreshAvatar && $rawAvatar) {
                $user->avatar = $rawAvatar; // NEW
            }

            // Jika belum punya password sama sekali, generate & kirim link set password
            if (empty($user->password)) {
                $user->password = Hash::make(Str::random(32));
                Password::sendResetLink(['email' => $user->email]);
            }

            // Verified-kan email (email Google dianggap valid)
            if (is_null($user->email_verified_at)) {
                $user->email_verified_at = Carbon::now();
            }

            $user->save(); // NEW: pastikan perubahan tersimpan
        }

        Auth::login($user, true);
        // arahkan ke halaman asal yang disimpan
        $returnTo = session('oauth_return_to') ?: route('home');
        session()->forget('oauth_return_to');

        return redirect()->to($returnTo)->with('success','Login Google berhasil.');
    }

}

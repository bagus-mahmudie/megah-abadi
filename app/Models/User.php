<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;   // <- penting
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserAddress;

class User extends Authenticatable implements MustVerifyEmail  // <- tambahkan ini
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name','email','password','google_id','avatar','email_verified_at',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function cart()
    {
        return $this->hasOne(\App\Models\Cart::class);
    }

    public function getAvatarUrlAttribute(): string
    {
        $avatar = $this->avatar;

        if ($avatar) {
            // jika http(s) langsung pakai
            if (preg_match('~^https?://~', $avatar)) {
                return $avatar;
            }
            // coba dari disk public (storage:link)
            $path = ltrim($avatar, '/');
            if (Storage::disk('public')->exists($path)) {
                return Storage::url($path); // /storage/...
            }
            // fallback ke asset publik
            return asset($path);
        }

        // fallback ui-avatars (publik, tidak butuh login)
        $name = trim($this->name ?: 'User');
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) .
               '&background=E5E7EB&color=111827&size=128';
    }
    
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }
}

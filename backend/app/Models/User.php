<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\PemasukanKas;
use App\Models\PengeluaranKas;
use App\Models\GaleriKegiatan;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'otp_code',
        'otp_expires_at',
        'email_verified_at',
        'reset_password_otp',
        'reset_password_otp_expires_at',
        'reset_password_token',
        'reset_password_token_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function pemasukanKas()
    {
        return $this->hasMany(PemasukanKas::class, 'created_by');
    }

    public function pengeluaranKas()
    {
        return $this->hasMany(PengeluaranKas::class, 'created_by');
    }

    public function galeriKegiatan()
    {
        return $this->hasMany(GaleriKegiatan::class, 'created_by');
    }
}

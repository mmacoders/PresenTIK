<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'pangkat',
        'nrp',
        'nip',
        'email',
        'no_hp',
        'password',
        'role',
        'jabatan',
        'profile_pict',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_pict_url',
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

    /**
     * Get the absensi records for the user.
     */
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    /**
     * Get the izin records for the user.
     */
    public function izins(): HasMany
    {
        return $this->hasMany(Izin::class);
    }

    /**
     * Get the profile picture URL.
     */
    public function getProfilePictUrlAttribute()
    {
        if ($this->profile_pict) {
            return Storage::url($this->profile_pict);
        }
        
        return null;
    }

}
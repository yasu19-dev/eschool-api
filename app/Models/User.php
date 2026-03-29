<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Ajoute bien cette ligne en haut

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    // Ajoute HasApiTokens ici, avant HasFactory
    use HasApiTokens, HasFactory, Notifiable;
    use HasUuids;
    // use HasFactory, Notifiable;
    // Un User a un seul profil selon son rôle
    public function stagiaireProfile()
    {
        return $this->hasOne(StagiaireProfile::class);
    }

    public function formateurProfile()
    {
        return $this->hasOne(FormateurProfile::class);
    }

    public function adminProfile()
    {
        return $this->hasOne(AdminProfile::class);
    }

    // Un User peut avoir plusieurs rôles (Many-to-Many)
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
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
}

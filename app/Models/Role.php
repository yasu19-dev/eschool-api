<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Role extends Model
{
    use HasUuids;
    protected $fillable = [
        'code',
        'libelle',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}

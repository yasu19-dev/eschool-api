<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StaffMembre extends Model
{
    use HasUuids;
    protected $guarded = [];
    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }
}

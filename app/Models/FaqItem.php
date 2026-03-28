<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FaqItem extends Model
{
    use HasUuids;
    protected $guarded = [];

    public function categorie()
     { return $this->belongsTo(FaqCategorie::class, 'faq_categorie_id'); }
}

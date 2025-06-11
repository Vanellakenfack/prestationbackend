<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestataire extends Model {
    protected $fillable = ['metier', 'bio'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}

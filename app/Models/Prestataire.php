<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestataire extends Model {
    protected $fillable = ['metier', 'bio'];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function disponibilites(): HasMany
    {
        return $this->hasMany(Disponibilite::class);
    }

    public function horairesParDefaut()
    {
        return [
            'heure_debut' => '08:00',
            'heure_fin' => '18:00'
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disponibilite extends Model
{
    use HasFactory;

    protected $table = 'disponibilites'; // Assurez-vous que le nom de la table est correct

    protected $fillable = [
        'prestataire_id',
        'date',
        'morning',
        'afternoon',
        'evening',
    ];

    // CORRECTION ICI : Cast la colonne 'date' en type 'date' pour qu'elle soit un objet Carbon
    protected $casts = [
        'date' => 'date', // Ceci est la ligne clé
        'morning' => 'boolean',
        'afternoon' => 'boolean',
        'evening' => 'boolean',
    ];

    /**
     * Get the prestataire that owns the Disponibilite.
     */
    public function prestataire()
    {
        return $this->belongsTo(User::class, 'prestataire_id');
    }
}

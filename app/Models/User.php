<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'type'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relation avec les services (pour les prestataires)
    public function services()
    {
        return $this->hasMany(Service::class, 'prestataire_id');
    }

    // Vérifie si l'utilisateur est un prestataire
    public function isPrestataire()
    {
        return $this->type === 'prestataire';
    }

    // Vérifie si l'utilisateur est un client
    public function isClient()
    {
        return $this->type === 'client';
    }

    // Vérifie si l'utilisateur est un admin
    public function isAdmin()
    {
        return $this->type === 'admin';
    }
     public function prestataire()
    {
        return $this->hasOne(Prestataire::class);
    }
    public function client()
{
    return $this->hasOne(Client::class);
}
public function clientBookings()
{
    return $this->hasMany(\App\Models\Booking::class, 'client_id');
}

   
}
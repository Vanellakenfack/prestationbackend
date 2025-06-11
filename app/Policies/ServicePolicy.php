<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true; // Tous les utilisateurs peuvent voir la liste
    }

    public function view(User $user, Service $service)
    {
        return true; // Tous les utilisateurs peuvent voir un service
    }

    public function create(User $user)
    {
        return $user->isPrestataire();
    }

    public function update(User $user, Service $service)
    {
        return $user->isPrestataire() && $service->prestataire_id === $user->id;
    }

    public function delete(User $user, Service $service)
    {
        return $user->isPrestataire() && $service->prestataire_id === $user->id;
    }
}
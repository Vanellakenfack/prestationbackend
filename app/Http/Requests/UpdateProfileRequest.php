<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// app/Http/Requests/UpdateProfileRequest.php

class UpdateProfileRequest extends FormRequest
{
    public function rules() {
        $user = $this->user();
        $rules = [
            'email' => 'required|email|unique:users,email,' . $user->id,
        ];

        if ($user->type === 'prestataire') {
            $rules['metier'] = 'required|string';
        }

        return $rules;
    }
}
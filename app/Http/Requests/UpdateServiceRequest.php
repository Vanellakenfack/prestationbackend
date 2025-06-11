<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
{
    return [
        'titre' => 'sometimes|string|max:255',
        'description' => 'sometimes|string',
        'prix' => 'sometimes|numeric|min:0',
        'categorie' => 'sometimes|string|in:plomberie,electricite,jardinage,menage',
        'localisation' => 'sometimes|string',
        'duree_minutes' => 'sometimes|integer|min:15',
        'is_published' => 'sometimes|boolean'
    ];
}
}

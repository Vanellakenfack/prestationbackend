<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
        'titre' => 'required|string|max:255',
        'description' => 'required|string',
        'prix' => 'required|numeric|min:0',
        'categorie' => 'required|string|in:plomberie,electricite,jardinage,menage',
        'localisation' => 'required|string',
        'duree_minutes' => 'required|integer|min:15',
        'is_published' => 'sometimes|boolean'
    ];
}
}

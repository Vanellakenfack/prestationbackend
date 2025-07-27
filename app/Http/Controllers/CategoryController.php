<?php


namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Liste toutes les catégories
    public function index()
    {
        return response()->json(Category::all());
    }

    // Affiche une catégorie spécifique
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    // Crée une nouvelle catégorie
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:categories,name|max:255',
            'description' => 'nullable|string'
        ]);
        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    // Met à jour une catégorie
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:categories,name,' . $id . '|max:255',
            'description' => 'nullable|string'
        ]);
        $category->update($validated);
        return response()->json($category);
    }

    // Supprime une catégorie
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}
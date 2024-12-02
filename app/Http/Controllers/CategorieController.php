<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;  // Ajoute cette ligne pour utiliser OpenApi annotations


    /**
     * @OA\Schema(
     *     schema="Category",
     *     type="object",
     *     required={"name", "description"},
     *     @OA\Property(property="id", type="integer", format="int64"),
     *     @OA\Property(property="name", type="string"),
     *     @OA\Property(property="description", type="string")
     * )
     */



class CategorieController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     summary="Get all categories",
     *     tags={"Categorie"},
     *     @OA\Response(
     *         response=200,
     *         description="A list of categories",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Category"))
     *     )
     * )
     */
    // Pour la récupération de toutes les catégories existantes dans la BDD
    public function index() {

        $categories = Categorie::all();
        return response()->json($categories);

    }

    /**
     * @OA\Post(
     *     path="/api/v1/categories",
     *     summary="Create a new category",
     *     tags={"Categorie"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description"},
     *             @OA\Property(property="name", type="string", example="Electronics"),
     *             @OA\Property(property="description", type="string", example="Devices and gadgets")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    // Pour créer une catégorie
    public function store(Request $request) {

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'required|string|max:1000'
        ], [
            'name.required' => 'Le nom de l\'article est requis',
            'name.string' => 'Le nom de l\'article doit être une chaîne de caractères' ,
            'name.max' => 'Le nom de l\'article ne doit pas excéder 255 lettres',
            'name.unique' => 'Le nom de l\'article est déjà utilisé',
        ]);

        $categorie = Categorie::create($validatedData);

        return response()->json($categorie, 201);

    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/{id}",
     *     summary="Get category by ID",
     *     tags={"Categorie"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category details",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    // Pour afficher une catégorie par son ID
    public function show($id) {

        $category = Categorie::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/categories/{id}",
     *     summary="Update a category",
     *     tags={"Categorie"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description"},
     *             @OA\Property(property="name", type="string", example="Updated Category Name"),
     *             @OA\Property(property="description", type="string", example="Updated description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    // Pour faire une mise à jour
    public function update(Request $request, $id) {

        $category = Categorie::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category with ID : '.$id.' not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255|unique:categories,name',
            'description' => 'sometimes|string|max:1000'
        ]);

        $category->update($validatedData);
        return response()->json($category);

    }

    /**
     * @OA\Delete(
     *     path="/api/v1/categories/{id}",
     *     summary="Delete a category",
     *     tags={"Categorie"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category deleted successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    // Pour supprimer une catégorie
    public function destroy($id) {

        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json(['message' => 'Category with ID: '.$id.' not found'], 404);
        }

        $categorie->delete();
        return response()->json(['message' => 'Category deleted successfully!']);
    }
}

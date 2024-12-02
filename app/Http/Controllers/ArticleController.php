<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;  // N'oublie pas d'ajouter cette ligne pour utiliser OpenApi annotations

/**
 * @OA\Schema(
 *     schema="Article",
 *     type="object",
 *     required={"name", "priceInit", "priceSell", "available_quantity", "categorie_id"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="priceInit", type="number", format="float"),
 *     @OA\Property(property="priceSell", type="number", format="float"),
 *     @OA\Property(property="available_quantity", type="integer"),
 *     @OA\Property(property="categorie_id", type="integer")
 * )
 */

class ArticleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/articles",
     *     summary="Get all articles",
     *     tags={"Article"},
     *     @OA\Response(
     *         response=200,
     *         description="A list of articles",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Article"))
     *     )
     * )
     */
    // Pour avoir la liste de tous les articles existants dans la BDD
    public function getAllArticles() {

        $articles = Article::with('categorie')->get(); // Pour inclure les données de Catégorie
        return response()->json($articles);

    }

    /**
     * @OA\Post(
     *     path="/api/v1/article/create",
     *     summary="Create a new article",
     *     tags={"Article"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "priceInit", "available_quantity", "categorie_id"},
     *             @OA\Property(property="name", type="string", example="New Article"),
     *             @OA\Property(property="priceInit", type="number", format="float", example="100.00"),
     *             @OA\Property(property="available_quantity", type="integer", example="50"),
     *             @OA\Property(property="categorie_id", type="integer", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Article created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    // Pour créer un article dans la BDD
    public function createArticle(Request $request) {

        $validatedArticle = $request->validate([
            'name' => 'required|string|max:255|unique:articles,name',
            'priceInit' => 'required|numeric|min:0',
            'available_quantity' => 'required|integer|min:0',
            'categorie_id' => 'required|integer|exists:categories,id',
        ], [
            'name.required' => 'Le nom de l\'article est requis',
            'name.string' => 'Le nom de l\'article doit être une chaîne de caractères' ,
            'name.max' => 'Le nom de l\'article ne doit pas excéder 255 lettres',
            'name.unique' => 'Le nom de l\'article est déjà utilisé',
            'categorie_id.required' => "L'identifiant de la categorie est obligatoire.",
            'categorie_id.integer' => "L'identifiant de la categorie doit être un nombre entier.",
            'categorie_id.exists' => "La categorie sélectionné n'existe pas.",

        ]);

        // Pour le calcul de la valeur de priceSell
        $validatedArticle['priceSell'] = $validatedArticle['priceInit'] + $validatedArticle['priceInit'] * 0.10;
    
        $article = Article::create($validatedArticle);
        return response()->json($article, 201);
    
    }

    /**
     * @OA\Get(
     *     path="/api/v1/article/{id}",
     *     summary="Get article by ID",
     *     tags={"Article"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the article",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article details",
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found"
     *     )
     * )
     */
    // Pour récupérer un article par son ID
    public function getArticleById($id) {

        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        return response()->json($article);

    }

    /**
     * @OA\Put(
     *     path="/api/v1/article/{id}",
     *     summary="Update article by ID",
     *     tags={"Article"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the article",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "priceInit", "available_quantity", "categorie_id"},
     *             @OA\Property(property="name", type="string", example="Updated Article Name"),
     *             @OA\Property(property="priceInit", type="number", format="float", example="120.00"),
     *             @OA\Property(property="available_quantity", type="integer", example="70"),
     *             @OA\Property(property="categorie_id", type="integer", example="2")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found"
     *     )
     * )
     */
    // Pour mettre à jour un article par son ID
    public function updateArticleById(Request $request, $id) {

        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found to be updated'], 404);
        }

        $validatedArticle = $request->validate([
            'name' => 'sometimes|string|max:255|unique:articles,name',
            'priceInit' => 'sometimes|numeric|min:0',
            'available_quantity' => 'sometimes|integer|min:0',
            'categorie_id' => 'required|integer|exists:categories,id',
        ], [
            'name.string' => 'Le nom de l\'article doit être une chaîne de caractères' ,
            'name.max' => 'Le nom de l\'article ne doit pas excéder 255 lettres',
            'name.unique' => 'Le nom de l\'article est déjà utilisé',
            'categorie_id.required' => "L'identifiant de la categorie est obligatoire.",
            'categorie_id.integer' => "L'identifiant de la categorie doit être un nombre entier.",
            'categorie_id.exists' => "La categorie sélectionné n'existe pas.",

        ]);

        $validatedArticle['priceSell'] = $validatedArticle['priceInit'] + $validatedArticle['priceInit'] * 0.10;

        $article->update($validatedArticle);
        return response()->json($article);

    }

    /**
     * @OA\Delete(
     *     path="/api/v1/article/{id}",
     *     summary="Delete article by ID",
     *     tags={"Article"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the article to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Article was deleted successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found"
     *     )
     * )
     */
    // Pour supprimer un article par son ID
    public function deleteArticleById($id) {

        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        $article->delete();

        return response()->json(['message' => 'Article was deleted successfully!']);
    }
}

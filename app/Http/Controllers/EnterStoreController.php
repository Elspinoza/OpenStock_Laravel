<?php

namespace App\Http\Controllers;

use App\Models\EnterStore;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;  // N'oublie pas d'ajouter cette ligne pour utiliser OpenApi annotations

/**
 * @OA\Schema(
 *     schema="EnterStore",
 *     type="object",
 *     required={"quantity", "article_id"},
 *     @OA\Property(property="quantity", type="integer", format="int32"),
 *     @OA\Property(property="article_id", type="integer", format="int32")
 * )
 */

class EnterStoreController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/enter/article",
     *     summary="Add a single item to the stock",
     *     tags={"EnterStore"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity", "article_id"},
     *             @OA\Property(property="quantity", type="integer", example=10, description="Quantity of the article being entered"),
     *             @OA\Property(property="article_id", type="integer", example=1, description="ID of the article")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Item successfully added to the stock",
     *         @OA\JsonContent(ref="#/components/schemas/EnterStore")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */

    public function storeEnter(Request $request) {

        $validatedData = $request->validate([
            'quantity' => 'required|integer|min:1',
            'article_id' => 'required|integer|exists:articles,id',
        ], [
            'quantity.required' => 'La quantité est obligatoire pour chaque article.',
            'quantity.integer' => 'La quantité doit être un nombre entier.',
            'quantity.min' => 'La quantité doit être une valeur positive supérieure ou égale à 1.',
            'article_id.required' => "L'identifiant de l'article est obligatoire.",
            'article_id.integer' => "L'identifiant de l'article doit être un nombre entier.", // Message personnalisé
            'article_id.exists' => "L'article sélectionné n'existe pas.",
        ]);

        $enterstore = EnterStore::create($validatedData);
        return response()->json($enterstore, 201);
    }


    /**
     * @OA\Post(
     *     path="/api/v1/enter/articles",
     *     summary="Add multiple items to the stock",
     *     tags={"EnterStore"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="List of articles to be entered",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"quantity", "article_id"},
     *                     @OA\Property(property="quantity", type="integer", example=5, description="Quantity of the article"),
     *                     @OA\Property(property="article_id", type="integer", example=1, description="ID of the article")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Items successfully added to the stock",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Entrées de stock enregistrés avec succès."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/EnterStore"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */

    public function storeManyEnter(Request $request)
    {
        // Validation des données pour plusieurs entrées
        $validatedData = $request->validate([
            'data' => 'required|array', // Le champ principal doit être un tableau
            'data.*.quantity' => 'required|integer|min:1', // Chaque élément doit avoir une quantité valide
            'data.*.article_id' => 'required|integer|exists:articles,id', // Chaque élément doit avoir un ID d'article valide
        ], [
            'data.*.quantity.required' => 'La quantité est obligatoire pour chaque article.',
            'data.*.quantity.integer' => 'La quantité doit être un nombre entier.',
            'data.*.quantity.min' => 'La quantité doit être une valeur positive supérieure ou égale à 1.',
            'data.*.article_id.required' => "L'identifiant de l'article est obligatoire.",
            'data.*.article_id.integer' => "L'identifiant de l'article doit être un nombre entier.", // Message personnalisé
            'data.*.article_id.exists' => "L'article sélectionné n'existe pas.",
        ]);

        // Traitement des sorties de stock
        $datas = [];
        foreach ($validatedData['data'] as $enterstoreData) {
            $datas[] = EnterStore::create($enterstoreData);
        }

        return response()->json([
            'message' => 'Entrées de stock enregistrés avec succès.',
            'data' => $datas,
        ], 201);
    }



    /**
     * @OA\Get(
     *     path="/api/v1/enter/articles/statistics",
     *     summary="Get overall statistics for entered stock",
     *     tags={"EnterStore"},
     *     @OA\Response(
     *         response=200,
     *         description="Statistics retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="total_quantity", type="integer", example=100, description="Total quantity of all entered items"),
     *             @OA\Property(
     *                 property="articles",
     *                 type="array",
     *                 description="List of articles with their total entered quantity",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="article_id", type="integer", example=1, description="ID of the article"),
     *                     @OA\Property(property="article_name", type="string", example="Article A", description="Name of the article"),
     *                     @OA\Property(property="quantity", type="integer", example=50, description="Total quantity entered for the article")
     *                     @OA\Property(property="priceInit", type="numeric", example=100, description="Initail price of entered for the article")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="most_enter",
     *                 type="object",
     *                 description="The most entered article",
     *                 @OA\Property(property="article_id", type="integer", example=1),
     *                 @OA\Property(property="article_name", type="string", example="Article A"),
     *                 @OA\Property(property="quantity", type="integer", example=50)
     *                 @OA\Property(property="priceInit", type="numeric", example=100)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     )
     * )
     */


    public function getStatistics(Request $request)
    {
        // Quantité totale des articles sortis
        $totalQuantity = EnterStore::sum('quantity');

        // Quantité totale par article
        $articles = EnterStore::with('article')
            ->selectRaw('article_id, SUM(quantity) as total_quantity')
            ->groupBy('article_id')
            ->orderBy('total_quantity', 'desc')
            ->get()
            ->map(function ($enterstore) {
                return [
                    'article_id' => $enterstore->article_id,
                    'article_name' => $enterstore->article->name ?? 'N/A', // Suppose qu'un article a un champ "name"
                    'quantity' => $enterstore->total_quantity,
                    'priceInit' => $enterstore->article->priceInit,
                    $total = $enterstore->total_quantity * $enterstore->article->priceInit,
                    'soldeUse' => $total,
                ];
            });

        // Article le plus vendu
        $mostEnter = $articles->first();

        //$totalSoldeUsed = $enterstore->

        return response()->json([
            'total_quantity' => $totalQuantity,
            'articles' => $articles,
            'most_enter' => $mostEnter,
        
        ]);
    }



    /**
     * @OA\Get(
     *     path="/api/v1/enter/articles/statistics/period",
     *     summary="Get stock entry statistics filtered by date range",
     *     tags={"EnterStore"},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date for filtering (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-11-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date for filtering (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-11-30")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statistics retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="total_quantity", type="integer", example=50, description="Total quantity of all entered items in the selected date range"),
     *             @OA\Property(
     *                 property="articles",
     *                 type="array",
     *                 description="List of articles with their total entered quantity in the date range",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="article_id", type="integer", example=2, description="ID of the article"),
     *                     @OA\Property(property="article_name", type="string", example="Article B", description="Name of the article"),
     *                     @OA\Property(property="quantity", type="integer", example=30, description="Total quantity entered for the article")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="most_enter",
     *                 type="object",
     *                 description="The most entered article in the date range",
     *                 @OA\Property(property="article_id", type="integer", example=2),
     *                 @OA\Property(property="article_name", type="string", example="Article B"),
     *                 @OA\Property(property="quantity", type="integer", example=30)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */

    public function getStatisticsWithDate(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = EnterStore::with('article');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalQuantity = $query->sum('quantity');

        $articles = $query
            ->selectRaw('article_id, SUM(quantity) as total_quantity')
            ->groupBy('article_id')
            ->orderBy('total_quantity', 'desc')
            ->get()
            ->map(function ($enterstore) {
                return [
                    'article_id' => $enterstore->article_id,
                    'article_name' => $enterstore->article->name ?? 'N/A',
                    'quantity' => $enterstore->total_quantity,
                ];
            });

        $mostEnter = $articles->first();

        return response()->json([
            'total_quantity' => $totalQuantity,
            'articles' => $articles,
            'most_enter' => $mostEnter,
        ]);
    }


}

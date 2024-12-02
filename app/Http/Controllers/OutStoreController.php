<?php

namespace App\Http\Controllers;

use App\Models\OutStore;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;  
use App\Models\Article;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Schema(
 *     schema="OutStore",
 *     type="object",
 *     required={"quantity", "solde", "soldeTotal", "article_id"},
 *     @OA\Property(property="quantity", type="integer", format="int32"),
 *     @OA\Property(property="solde", type="number", format="float"),
 *     @OA\Property(property="soldeTotal", type="number", format="float"),
 *     @OA\Property(property="article_id", type="integer", format="int32")
 * )
 */

class OutStoreController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/sell/article",
     *     summary="Create a single OutStore entry",
     *     description="Enregistre une seule sortie d'article du stock.",
     *     tags={"OutStore"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity", "article_id"},
     *             @OA\Property(property="quantity", type="integer", example=5),
     *             @OA\Property(property="article_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Article removed from stock successfully",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/OutStore"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */

    
    public function storeSell(Request $request) {

        $validatedData = $request->validate([
            'quantity' => 'required|integer|min:1',
            'article_id' => 'required|integer|exists:articles,id',
        ],[
            'quantity.required' => 'La quantité est obligatoire pour chaque article.',
            'quantity.integer' => 'La quantité doit être un nombre entier.',
            'quantity.min' => 'La quantité doit être une valeur positive supérieure ou égale à 1.',
            'article_id.required' => "L'identifiant de l'article est obligatoire.",
            'article_id.integer' => "L'identifiant de l'article doit être un nombre entier.",
            'article_id.exists' => "L'article sélectionné n'existe pas.",
        ]);


        // Récupération de l'article pour vérifier le stock disponible
        $article = Article::find($validatedData['article_id']);
        
        

        if ($article->available_quantity < $validatedData['quantity']) {
            return response()->json([
                'message' => 'La quantité demandée est insuffisante ou épuisée.',
                'available_stock' => $article->available_quantity,
            ], 400);
        }

        $validatedData['solde'] = $article->priceSell * $validatedData['quantity'];
        $validatedData['soldeTotal'] = $validatedData['solde'];


        // Mise à jour du stock après vérification
        //$article->available_quantity -= $validatedData['quantity'];
        $article -> decrement('available_quantity', $validatedData['quantity']);
        $article->save();

        $outstore = OutStore::create($validatedData);
        return response()->json([$outstore,
        'solde' => $outstore->solde ,
        'soldeTotal' => $validatedData['soldeTotal'] ,
    ],
        201);

    }



    /**
     * @OA\Post(
     *     path="/api/v1/sell/articles",
     *     summary="Create multiple OutStore entries",
     *     description="Enregistre plusieurs sorties d'articles du stock en une seule requête.",
     *     tags={"OutStore"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *           required={"data"},
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"quantity", "article_id"},
     *                     @OA\Property(property="quantity", type="integer", example=5),
            *              @OA\Property(property="article_id", type="integer", example=2)
     *                 )
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Articles removed from stock successfully",
     *           @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Sorties de stock créées avec succès."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/OutStore")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */


    public function outStoreMany(Request $request)
    {
        // Validation des données pour plusieurs entrées
        $validatedData = $request->validate([
            'data' => 'required|array',
            'data.*.quantity' => 'required|integer|min:1',
            'data.*.article_id' => 'required|integer|exists:articles,id',
        ], [
            'data.*.quantity.required' => 'La quantité est obligatoire pour chaque article.',
            'data.*.quantity.integer' => 'La quantité doit être un nombre entier.',
            'data.*.quantity.min' => 'La quantité doit être une valeur positive supérieure ou égale à 1.',
            'data.*.article_id.required' => "L'identifiant de l'article est obligatoire.",
            'data.*.article_id.integer' => "L'identifiant de l'article doit être un nombre entier.",
            'data.*.article_id.exists' => "L'article sélectionné n'existe pas.",
        ]);
    
        $datas = [];
        $errors = [];
        $soldeTotal = 0;
    
        DB::beginTransaction(); // Démarrage de la transaction
    
        try {
            foreach ($validatedData['data'] as $outstoreData) {
                $article = Article::find($outstoreData['article_id']);
    
                // Vérification du stock disponible
                if ($article->available_quantity < $outstoreData['quantity']) {
                    $errors[] = [
                        'article_id' => $outstoreData['article_id'],
                        'message' => 'La quantité demandée est supérieure à la quantité disponible.',
                        'available_quantity' => $article->available_quantity,
                    ];
                    continue;
                }
    
                // Mise à jour du stock
                $article->available_quantity -= $outstoreData['quantity'];
                $article->save();
    
                // Calcul du solde
                $solde = $outstoreData['quantity'] * $article->priceSell;
                $soldeTotal += $solde;
    
                // Création de l'enregistrement
                $outstoreRecord = OutStore::create([
                    'quantity' => $outstoreData['quantity'],
                    'article_id' => $outstoreData['article_id'],
                    'solde' => $solde,
                ]);
    
                $datas[] = $outstoreRecord;
            }
    
            // Si des erreurs ont été rencontrées, rollback
            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Certains articles n\'ont pas pu être enregistrés en raison d\'un stock insuffisant.',
                    'errors' => $errors,
                    'success' => $datas,
                    'soldeTotal' => $soldeTotal,
                ], 400);
            }
    
            DB::commit(); // Validation de la transaction
    
            return response()->json([
                'message' => 'Sorties de stock créées avec succès.',
                'data' => $datas,
                'soldeTotal' => $soldeTotal,
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack(); // Annulation en cas d'erreur
    
            return response()->json([
                'message' => 'Une erreur est survenue lors du traitement des sorties de stock.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
 
 
    /**
     * @OA\Get(
     *     path="/api/v1/sell/articles/statistics",
     *     summary="Retrieve statistics on article sales",
     *     description="Obtient des statistiques sur les sorties d'articles, comme la quantité totale et les articles les plus sortis.",
     *     tags={"OutStore"},
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques récupérées avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="total_quantity", type="integer", example=100),
     *             @OA\Property(property="articles", type="array", 
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="article_id", type="integer", example=1),
     *                 @OA\Property(property="article_name", type="string", example="Article A"),
     *                 @OA\Property(property="quantity", type="integer", example=50)
     *             )),
     *             @OA\Property(property="most_sold", type="object",
     *                 @OA\Property(property="article_id", type="integer", example=1),
     *                 @OA\Property(property="article_name", type="string", example="Article A"),
     *                 @OA\Property(property="quantity", type="integer", example=50)
     *             )
     *         )
     *     )
     * )
     */
    public function getStatistics(Request $request)
    {
        // Quantité totale des articles sortis
        $totalQuantity = OutStore::sum('quantity');

        // Quantité totale par article
        $articles = OutStore::with('article')
            ->selectRaw('article_id, SUM(quantity) as total_quantity')
            ->groupBy('article_id')
            ->orderBy('total_quantity', 'desc')
            ->get()
            ->map(function ($outstore) {
                return [
                    'article_id' => $outstore->article_id,
                    'article_name' => $outstore->article->name ?? 'N/A', // Suppose qu'un article a un champ "name"
                    'quantity' => $outstore->total_quantity,
                    'priceSell' => $outstore->article->priceSell,
                    //'solde' => $outstore->solde,
                    $solde = $outstore->total_quantity * $outstore->article->priceSell,
                    'solde' => $solde,
                    //'soldeTotal' => $outstore->soldeTotal,
                    
                ];
            });
            

        // Article le plus vendu
        $mostSold = $articles->first();

        $soldeTotal = OutStore::sum('solde');

        return response()->json([
            'total_quantity' => $totalQuantity,
            'articles' => $articles,
            'most_sold' => $mostSold,
            'soldeTotal' => $soldeTotal,
        ]);
    }



    /**
     * @OA\Get(
     *     path="/api/v1/sell/articles/statistics/period",
     *     summary="Retrieve statistics on article sales",
     *     description="Obtient des statistiques sur les sorties d'articles, comme la quantité totale, les articles les plus sortis et les sorties dans une plage de dates.",
     *     tags={"OutStore"},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         description="Date de début pour filtrer les statistiques (format: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2024-11-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         description="Date de fin pour filtrer les statistiques (format: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2024-11-30")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques récupérées avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="total_quantity", type="integer", description="Quantité totale des articles sortis", example=150),
     *             @OA\Property(
     *                 property="articles",
     *                 type="array",
     *                 description="Liste des articles avec leurs statistiques",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="article_id", type="integer", description="ID de l'article", example=1),
     *                     @OA\Property(property="article_name", type="string", description="Nom de l'article", example="Article A"),
     *                     @OA\Property(property="quantity", type="integer", description="Quantité totale sortie pour cet article", example=50)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="most_sold",
     *                 type="object",
     *                 description="Article le plus vendu dans la période sélectionnée",
     *                 @OA\Property(property="article_id", type="integer", description="ID de l'article", example=3),
     *                 @OA\Property(property="article_name", type="string", description="Nom de l'article", example="Article C"),
     *                 @OA\Property(property="quantity", type="integer", description="Quantité totale sortie pour cet article", example=70)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide (exemple : format de date incorrect)"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur"
     *     )
     * )
     */

    public function getStatisticsWithDate(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = OutStore::with('article');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalQuantity = $query->sum('quantity');

        $articles = $query
            ->selectRaw('article_id, SUM(quantity) as total_quantity')
            ->groupBy('article_id')
            ->orderBy('total_quantity', 'desc')
            ->get()
            ->map(function ($outstore) {
                return [
                    'article_id' => $outstore->article_id,
                    'article_name' => $outstore->article->name ?? 'N/A',
                    'quantity' => $outstore->total_quantity,
                ];
            });

        $mostSold = $articles->first();

        return response()->json([
            'total_quantity' => $totalQuantity,
            'articles' => $articles,
            'most_sold' => $mostSold,
        ]);
    }

}

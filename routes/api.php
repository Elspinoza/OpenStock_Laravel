<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\EnterStoreController;
use App\Http\Controllers\OutStoreController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




// Endpoint de Catégories
Route::apiResource('/v1/categories', CategorieController::class);


// Endpoint pour Article
Route::get('/v1/articles', [ArticleController::class, 'getAllArticles']);
Route::post('/v1/article/create', [ArticleController::class, 'createArticle']);
Route::get('/v1/article/{id}', [ArticleController::class, 'getArticleById']);
Route::put('/v1/article/{id}', [ArticleController::class, 'updateArticleById']);
Route::delete('/v1/article/{id}', [ArticleController::class, 'deleteArticleById']);


// Endpoint pour l'entrer des Articles
//Route::apiResource('/v1/enter/article', EnterStoreController::class);
Route::post('/v1/enter/article', [EnterStoreController::class, 'storeEnter']);
Route::post('/v1/enter/articles', [EnterStoreController::class, 'storeManyEnter']);
Route::get('/v1/enter/articles/statistics', [EnterStoreController::class, 'getStatistics']);
Route::get('/v1/enter/articles/statistics/period', [EnterStoreController::class, 'getStatisticsWithDate']);



// Endpoint pour la sortie des Articles
//Route::apiResource('/v1/sell/article', OutStoreController::class);
Route::post('/v1/sell/article', [OutStoreController::class, 'storeSell']);
Route::post('/v1/sell/articles', [OutStoreController::class, 'outStoreMany']);
Route::get('/v1/sell/articles/statistics', [OutStoreController::class, 'getStatistics']);
Route::get('/v1/sell/articles/statistics/period', [OutStoreController::class, 'getStatisticsWithDate']);

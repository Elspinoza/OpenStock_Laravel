<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategorieController;

Route::get('/', function () {
    return view('welcome');
});


// Endpoint de Catégories
//Route::apiResource('/api/v1/categories', CategorieController::class);

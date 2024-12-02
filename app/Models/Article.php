<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{

    protected $fillable = [
        'name',
        'priceInit',
        'priceSell',
        'available_quantity',
        'categorie_id'
    ];

    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory;

    public function categorie() {
        return $this->belongsTo(Categorie::class);
    }

    public function enterStores() {
        return $this -> hasMany(EnterStore::class);
    }

    public function outStores() {
        return $this -> hasMany(OutStore::class);
    }

}

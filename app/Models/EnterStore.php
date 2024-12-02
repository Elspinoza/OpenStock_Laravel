<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnterStore extends Model
{

    protected $fillable = [
        'quantity',
        'article_id'  
    ];

    /** @use HasFactory<\Database\Factories\EnterStoreFactory> */
    use HasFactory;

    public function article() {
        return $this -> belongsTo(Article::class);
    }

    protected static function booted()
    {
        static::created(function($enterstore) {
           
            $article = $enterstore -> article;
            
            $article -> available_quantity += $enterstore -> quantity;
            
            $article -> save();
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutStore extends Model
{

    protected $fillable = [
        'quantity',
        'article_id',
        'solde'
    ];

    /** @use HasFactory<\Database\Factories\OutStoreFactory> */
    use HasFactory;

    public function article() {
        return $this -> belongsTo(Article::class);
    }

    // La méthode "booted" est appelée automatiquement lorsque le modèle "EnterStore" est chargé.
   /*  protected static function booted()
    {

        // L'événement "created" est un événement qui se déclenche après la création d'un enregistrement.
        // Ici, on utilise la méthode "created" pour définir l'action qui doit être exécutée après la création d'un enregistrement EnterStore.
        static::created(function($outstore) {
           
            // $enterStore est l'instance du modèle "EnterStore" qui a été créée.
            // On utilise "article" pour récupérer l'article associé à cette entrée en magasin (via la relation "belongsTo").
            $article = $outstore -> article;
            
            // Ici, on accède à la propriété "available_quantity" de l'article et on ajoute la quantité de l'entrée en magasin.
            $article -> available_quantity -= $outstore -> quantity;
            
             // Après avoir mis à jour la quantité disponible, on sauvegarde l'article avec la nouvelle quantité.
            $article -> save();
            
        });
    } */
}

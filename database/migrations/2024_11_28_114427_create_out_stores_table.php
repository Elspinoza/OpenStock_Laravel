<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('out_stores', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('quantity');
            $table->decimal('solde', 10, 2)->default(0);
            //$table->decimal('soldeTotal', 10, 2)->default(0);
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('out_stores');
    }
};

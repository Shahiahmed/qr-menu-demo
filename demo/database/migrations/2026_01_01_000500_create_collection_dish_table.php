<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Many-to-many between collections and dishes. A dish can belong to several
 * curated collections regardless of its own category.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_dish', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->unique(['collection_id', 'dish_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_dish');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Curated groups ("Рекомендации", "Летнее предложение") shown as horizontal
 * rails above the menu. A dish reaches a collection through the pivot, so it can
 * appear in several collections at once, crossing its own category.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('name_ru');
            $table->string('name_kk')->nullable();
            $table->unsignedInteger('sort')->default(0)->index();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};

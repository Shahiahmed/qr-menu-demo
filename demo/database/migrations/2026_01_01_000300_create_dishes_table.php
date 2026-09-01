<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dishes. Price is stored in minor units (тиын, 1/100 ₸) as an unsigned integer
 * — never a float, so the bill never drifts on rounding. `slug` is unique and
 * feeds the per-dish SEO page (/d/{slug}) — the Premium selling point. Two
 * booleans: `is_available` is the stop-list (shown but marked "sold out"),
 * `is_visible` hides the dish from guests entirely.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('menu_category_id')
                ->constrained('menu_categories')
                ->cascadeOnDelete();

            $table->string('slug')->unique();

            $table->string('name_ru');
            $table->string('name_kk')->nullable();
            $table->text('description_ru')->nullable();
            $table->text('description_kk')->nullable();

            // Minor units (тиын). unsignedBigInteger so large tabs never overflow.
            $table->unsignedBigInteger('price')->default(0);

            $table->boolean('is_available')->default(true); // stop-list
            $table->boolean('is_visible')->default(true);    // hidden from guests

            $table->string('image_path')->nullable();

            $table->unsignedInteger('sort')->default(0)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};

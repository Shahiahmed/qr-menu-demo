<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menu sections (Завтраки, Салаты, …). Bilingual names; Kazakh is nullable so a
 * venue can start in Russian and translate later. `sort` drives display order,
 * `is_visible` hides a whole section from guests without deleting it.
 *
 * Two-level taxonomy via a self-reference: `parent_id` null = a top-level
 * category (Кухня, Бар); non-null = a subcategory under it (Салаты in Кухня).
 * Dishes may attach to either level, so a flat category (Сигареты — items
 * straight under the top) and a nested one coexist. Deleting a parent nulls its
 * children (they float up to top level) rather than cascading the whole branch.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();

            // Self-reference for the subcategory level. Nullable → top-level.
            $table->foreignId('parent_id')->nullable()->constrained('menu_categories')->nullOnDelete();

            $table->string('name_ru');
            $table->string('name_kk')->nullable();

            $table->unsignedInteger('sort')->default(0)->index();
            $table->boolean('is_visible')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_categories');
    }
};

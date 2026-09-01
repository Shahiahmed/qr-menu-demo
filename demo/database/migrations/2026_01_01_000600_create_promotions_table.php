<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Promo banners ("сторис") — the swipeable cards at the top of the guest menu:
 * happy-hour offers, seasonal specials. Image is optimized to WebP on upload.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title_ru');
            $table->string('title_kk')->nullable();
            $table->string('subtitle_ru')->nullable();
            $table->string('subtitle_kk')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort')->default(0)->index();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Line items of an order. We snapshot the dish name (both locales) and price at
 * order time, so a later menu edit — a rename, a price change, or deleting the
 * dish — never rewrites history. `dish_id` is kept (nullOnDelete) only as a soft
 * link back to the live dish; the snapshot is the source of truth for the bill.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Soft link — the dish may later be edited or removed; the snapshot
            // below is what the bill is built from.
            $table->foreignId('dish_id')
                ->nullable()
                ->constrained('dishes')
                ->nullOnDelete();

            $table->string('name_ru');
            $table->string('name_kk')->nullable();

            // Minor units (тиын), snapshot of the price at order time.
            $table->unsignedBigInteger('price')->default(0);

            $table->unsignedSmallInteger('quantity')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

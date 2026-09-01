<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guest orders placed from the table. Single-tenant, so no tenant_id — every
 * order belongs to the one venue this site is for.
 *
 * `total` is stored in minor units (тиын) and is always recomputed on the server
 * from live dish prices at submit time — the client price is never trusted.
 * `status` drives the kitchen workflow (new → accepted → ready → done, or
 * cancelled); staff advance it from the Filament panel. `table_number` is
 * nullable so a venue can also take counter orders with no table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('table_number')->nullable();

            // Minor units (тиын). unsignedBigInteger so large tabs never overflow.
            $table->unsignedBigInteger('total')->default(0);

            // Kitchen workflow state — see Order::STATUSES.
            $table->string('status', 20)->default('new')->index();

            // Free-text note from the guest (allergies, "no onion", …).
            $table->string('comment', 500)->nullable();

            // Language the guest ordered in — lets staff read names in context.
            $table->string('locale', 5)->default('ru');

            $table->timestamps();

            // The panel lists newest-first and filters by state; index the pair.
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

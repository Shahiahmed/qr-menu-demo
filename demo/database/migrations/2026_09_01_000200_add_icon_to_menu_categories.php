<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Optional per-category icon, chosen by the owner in the admin panel and shown
// on the guest subcategory chips. Stores a short key from config('menu.category_icons');
// null means no icon. String, not FK — the option set lives in config, not a table.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->string('icon', 40)->nullable()->after('name_kk');
        });
    }

    public function down(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The venue itself. This is a single-tenant site — one restaurant per deploy —
 * so the whole venue lives in one row (id = 1), managed from the Filament panel.
 * Unlike the multi-tenant SaaS there is no `tenant_id`: everything here belongs
 * to the one venue this site is for.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_settings', function (Blueprint $table) {
            $table->id();

            $table->string('name')->default('Заведение');
            // ISO-ish currency code; prices are stored in minor units (тиын).
            $table->string('currency', 8)->default('KZT');
            // Language the menu opens in. `kk` is Kazakh (route/UI say "kz").
            $table->string('default_locale', 5)->default('ru');

            // Bilingual "about" blurb shown under the cover.
            $table->text('description_ru')->nullable();
            $table->text('description_kk')->nullable();

            // Contact chips overlaid on the cover.
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('wifi_ssid')->nullable();
            $table->string('wifi_password')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('tiktok_url')->nullable();

            // Look. `theme` recolours (accent family only), `layout` rearranges.
            // These are the guest's *default* — the guest can override both for
            // themselves in the on-page Settings sheet (kept in localStorage).
            $table->string('theme', 20)->default('classic');
            $table->string('layout', 20)->default('classic');

            // Uploaded imagery (relative paths on the public disk).
            $table->string('cover_path')->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('show_logo')->default(true);

            // Ordering. Guests order from the table; they pick a table number at
            // checkout, and a per-table QR can prefill it via ?table=N. When set,
            // the guest gets a 1..N dropdown; null/0 → a free numeric entry.
            $table->boolean('ordering_enabled')->default(true);
            $table->unsignedSmallInteger('tables_count')->nullable();

            // Homepage SEO overrides (fall back to name/description when blank).
            $table->string('seo_title_ru')->nullable();
            $table->string('seo_title_kk')->nullable();
            $table->string('seo_description_ru')->nullable();
            $table->string('seo_description_kk')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_settings');
    }
};

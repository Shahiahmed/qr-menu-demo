<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rounds out the venue's SEO/OG controls so the owner can manage everything
 * from the panel: search keywords per locale and a dedicated social-share
 * (Open Graph) image, separate from the on-page cover.
 *
 * Also backfills sensible bilingual SEO defaults for the single venue row when
 * they are still blank — the site should ship with real meta tags, not empty
 * ones. This runs once (migrations are tracked) and only fills empty columns,
 * so it never clobbers anything the owner has already edited in the panel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venue_settings', function (Blueprint $table) {
            $table->string('seo_keywords_ru')->nullable()->after('seo_description_kk');
            $table->string('seo_keywords_kk')->nullable()->after('seo_keywords_ru');
            // Dedicated OG/social image; the head falls back to the cover when blank.
            $table->string('seo_og_path')->nullable()->after('seo_keywords_kk');
        });

        $venue = DB::table('venue_settings')->where('id', 1)->first();

        if (! $venue) {
            return;
        }

        $defaults = [
            'seo_title_ru' => 'Дастархан — меню ресторана в Алматы | QR-меню',
            'seo_title_kk' => 'Дастархан — Алматыдағы мейрамхана мәзірі | QR-мәзір',
            'seo_description_ru' => 'Меню ресторана «Дастархан» в Алматы: казахская и европейская кухня, свежие блюда, честные цены. Фото, цены и заказ прямо со стола по QR-коду.',
            'seo_description_kk' => '«Дастархан» мейрамханасының мәзірі, Алматы: қазақ және еуропа асханасы, балғын тағамдар, әділ бағалар. Фото, баға және үстелден QR арқылы тапсырыс.',
            'seo_keywords_ru' => 'меню, ресторан Алматы, казахская кухня, европейская кухня, бешбармак, QR меню, заказ еды, кафе Алматы',
            'seo_keywords_kk' => 'мәзір, Алматы мейрамханасы, қазақ асханасы, еуропа асханасы, бешбармақ, QR мәзір, тағам тапсырысы',
        ];

        $fill = [];

        foreach ($defaults as $column => $value) {
            if (empty($venue->{$column})) {
                $fill[$column] = $value;
            }
        }

        if ($fill !== []) {
            DB::table('venue_settings')->where('id', 1)->update($fill);
        }
    }

    public function down(): void
    {
        Schema::table('venue_settings', function (Blueprint $table) {
            $table->dropColumn(['seo_keywords_ru', 'seo_keywords_kk', 'seo_og_path']);
        });
    }
};

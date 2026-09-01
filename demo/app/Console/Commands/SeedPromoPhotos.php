<?php

namespace App\Console\Commands;

use App\Models\Promotion;
use App\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Fills the demo promo banners with stock photos so the top of the menu looks
 * finished for showcasing. Placeholder imagery only — a real venue swaps these
 * for its own art via the admin. Idempotent: re-running (with --force) fetches
 * fresh photos and deletes the previous file. Run manually, never in deploy.sh.
 *
 * Promos have no slug, so photos are keyed on the Russian title (the same key
 * the seeder upserts on). Landscape source (16:9-ish) suits the wide banner,
 * which the CSS then object-fit: cover crops. Same pipeline as dish photos:
 * LoremFlickr → ImageOptimizer → WebP.
 */
class SeedPromoPhotos extends Command
{
    protected $signature = 'demo:promo-photos {--force : Replace photos that promos already have}';

    protected $description = 'Attach stock photos to the demo promo banners (placeholders for the showcase)';

    /**
     * title_ru => search tags for the stock photo.
     *
     * @var array<string,string>
     */
    private const PHOTOS = [
        'Счастливые часы' => 'coffee,cafe',
        'Бизнес-ланч' => 'lunch,restaurant',
        'День рождения' => 'birthday,cake',
    ];

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $done = 0;
        $skipped = 0;
        $failed = 0;

        foreach (self::PHOTOS as $title => $tags) {
            $promo = Promotion::query()->where('title_ru', $title)->first();

            if (! $promo) {
                $this->warn("• {$title}: промо не найдено, пропуск");
                $skipped++;

                continue;
            }

            if ($promo->image_path && ! $force) {
                $this->line("• {$title}: фото уже есть, пропуск (--force чтобы заменить)");
                $skipped++;

                continue;
            }

            // Deterministic per promo so re-runs keep the same picture; a wide
            // 1200×675 source gives the 16:9 banner room without upscaling.
            $seed = crc32($title) % 100000;
            $url = "https://loremflickr.com/1200/675/{$tags}?lock={$seed}";

            try {
                $response = Http::timeout(25)->get($url);

                if (! $response->successful() || $response->body() === '') {
                    $this->error("• {$title}: загрузка не удалась (HTTP {$response->status()})");
                    $failed++;

                    continue;
                }

                $newPath = ImageOptimizer::storeBinary(
                    $response->body(), 'promos', ImageOptimizer::MODE_CONTAIN, 1200, 82,
                );

                $oldPath = $promo->image_path;
                $promo->update(['image_path' => $newPath]);

                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }

                $this->info("✓ {$title}: {$newPath}");
                $done++;
            } catch (\Throwable $e) {
                $this->error("• {$title}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Готово: загружено {$done}, пропущено {$skipped}, ошибок {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

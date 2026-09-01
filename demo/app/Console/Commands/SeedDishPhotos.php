<?php

namespace App\Console\Commands;

use App\Models\Dish;
use App\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Fills every demo dish with a stock food photo so the menu looks finished for
 * showcasing. Placeholder imagery only — a real venue replaces these with its
 * own shots via the admin. Idempotent: re-running fetches fresh photos and
 * deletes the previous file. Run manually, never in deploy.sh.
 *
 * Photos come from LoremFlickr (tag-based Flickr placeholders); the same
 * `lock` seed keeps a dish's photo stable across runs. Each photo goes through
 * the normal ImageOptimizer pipeline (centre-crop square, WebP, ~800px).
 */
class SeedDishPhotos extends Command
{
    protected $signature = 'demo:dish-photos {--force : Replace photos that dishes already have}';

    protected $description = 'Attach stock food photos to the demo dishes (placeholders for the showcase)';

    /**
     * slug => search tags for the stock photo. Tags are chosen to look right for
     * the dish; the guest never sees them.
     *
     * @var array<string,string>
     */
    private const PHOTOS = [
        'kazhe' => 'oatmeal,porridge',
        'syrniki' => 'pancakes,cheese',
        'omlet' => 'omelette,eggs',
        'cezar' => 'caesar,salad',
        'grecheskiy' => 'greek,salad',
        'olivie' => 'salad',
        'beshbarmak' => 'meat,dish',
        'manty' => 'dumplings',
        'kuyrdak' => 'meat,stew',
        'dorado' => 'grilled,fish',
        'steyk' => 'steak',
        'plov' => 'pilaf,rice',
        'medovik' => 'honey,cake',
        'chizkeyk' => 'cheesecake',
        'chay' => 'tea,teapot',
        'ayran' => 'yogurt,drink',
        'kofe' => 'cappuccino,coffee',
    ];

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $done = 0;
        $skipped = 0;
        $failed = 0;

        foreach (self::PHOTOS as $slug => $tags) {
            $dish = Dish::query()->where('slug', $slug)->first();

            if (! $dish) {
                $this->warn("• {$slug}: блюдо не найдено, пропуск");
                $skipped++;
                continue;
            }

            if ($dish->image_path && ! $force) {
                $this->line("• {$slug}: фото уже есть, пропуск (--force чтобы заменить)");
                $skipped++;
                continue;
            }

            // Deterministic per dish so re-runs keep the same picture; 800px source
            // gives the square crop something to work with.
            $seed = crc32($slug) % 100000;
            $url = "https://loremflickr.com/800/800/{$tags}?lock={$seed}";

            try {
                $response = Http::timeout(25)->get($url);

                if (! $response->successful() || $response->body() === '') {
                    $this->error("• {$slug}: загрузка не удалась (HTTP {$response->status()})");
                    $failed++;
                    continue;
                }

                $newPath = ImageOptimizer::storeBinary(
                    $response->body(), 'dishes', ImageOptimizer::MODE_SQUARE, 800, 80,
                );

                $oldPath = $dish->image_path;
                $dish->update(['image_path' => $newPath]);

                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }

                $this->info("✓ {$slug}: {$newPath}");
                $done++;
            } catch (\Throwable $e) {
                $this->error("• {$slug}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Готово: загружено {$done}, пропущено {$skipped}, ошибок {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

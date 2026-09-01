<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Dish;
use App\Models\MenuCategory;
use App\Models\Promotion;
use App\Models\VenueSetting;
use Illuminate\Database\Seeder;

/**
 * The "Дастархан" sample menu — the same content the main project ships as its
 * live demo. Idempotent: safe to re-run. Prices are in тиын (minor units).
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        VenueSetting::query()->updateOrCreate(['id' => 1], [
            'name' => 'Дастархан',
            'currency' => 'KZT',
            'default_locale' => 'ru',
            'description_ru' => 'Уютное заведение казахской и европейской кухни в центре Алматы. Готовим по домашним рецептам из свежих продуктов.',
            'description_kk' => 'Алматының орталығындағы қазақ және еуропа асханасының жайлы мекемесі. Үй рецептері бойынша балғын өнімдерден дайындаймыз.',
            'address' => 'Алматы, пр. Достык, 132',
            'phone' => '+7 727 000 00 00',
            'wifi_ssid' => 'Dastarkhan_Guest',
            'wifi_password' => 'dastar2024',
            'instagram_url' => 'https://instagram.com/dastarkhan',
            'facebook_url' => 'https://facebook.com/dastarkhan',
            'tiktok_url' => 'https://tiktok.com/@dastarkhan',
            'theme' => 'classic',
            'layout' => 'classic',
            'show_logo' => true,
            'ordering_enabled' => true,
            'tables_count' => 12,
            'seo_title_ru' => 'Дастархан — меню ресторана в Алматы | QR-меню',
            'seo_title_kk' => 'Дастархан — Алматыдағы мейрамхана мәзірі | QR-мәзір',
            'seo_description_ru' => 'Меню ресторана «Дастархан» в Алматы: казахская и европейская кухня, свежие блюда, честные цены. Фото, цены и заказ прямо со стола по QR-коду.',
            'seo_description_kk' => '«Дастархан» мейрамханасының мәзірі, Алматы: қазақ және еуропа асханасы, балғын тағамдар, әділ бағалар. Фото, баға және үстелден QR арқылы тапсырыс.',
            'seo_keywords_ru' => 'меню, ресторан Алматы, казахская кухня, европейская кухня, бешбармак, QR меню, заказ еды, кафе Алматы',
            'seo_keywords_kk' => 'мәзір, Алматы мейрамханасы, қазақ асханасы, еуропа асханасы, бешбармақ, QR мәзір, тағам тапсырысы',
        ]);

        foreach ($this->menu() as $ci => $cat) {
            $category = $this->seedCategory($cat, $ci, null);

            // A top category either lists dishes directly (flat, like Напитки) or
            // groups them into subcategories (nested, like Кухня) — flexible.
            foreach ($cat['dishes'] ?? [] as $di => $dish) {
                $this->seedDish($dish, $category->id, $di);
            }

            foreach ($cat['children'] ?? [] as $si => $sub) {
                $subCategory = $this->seedCategory($sub, $si, $category->id);

                foreach ($sub['dishes'] ?? [] as $di => $dish) {
                    $this->seedDish($dish, $subCategory->id, $di);
                }
            }
        }

        $this->seedPromotions();
        $this->seedCollections();
    }

    /** @param array<string,mixed> $cat */
    private function seedCategory(array $cat, int $sort, ?int $parentId): MenuCategory
    {
        return MenuCategory::query()->updateOrCreate(
            ['name_ru' => $cat['name_ru']],
            [
                'name_kk' => $cat['name_kk'],
                'icon' => $cat['icon'] ?? null,
                'parent_id' => $parentId,
                'sort' => $sort,
                'is_visible' => true,
            ],
        );
    }

    /** @param array<string,mixed> $dish */
    private function seedDish(array $dish, int $categoryId, int $sort): void
    {
        Dish::query()->updateOrCreate(
            ['slug' => $dish['slug']],
            [
                'menu_category_id' => $categoryId,
                'name_ru' => $dish['name_ru'],
                'name_kk' => $dish['name_kk'],
                'description_ru' => $dish['description_ru'] ?? null,
                'description_kk' => $dish['description_kk'] ?? null,
                'price' => $dish['price'],
                'is_available' => $dish['is_available'] ?? true,
                'is_visible' => true,
                'image_path' => null,
                'sort' => $sort,
            ],
        );
    }

    /** Swipeable promo cards at the top of the guest menu. */
    private function seedPromotions(): void
    {
        $promotions = [
            ['title_ru' => 'Счастливые часы', 'title_kk' => 'Бақытты сағаттар', 'subtitle_ru' => 'Кофе −30% с 15:00 до 18:00', 'subtitle_kk' => '15:00-18:00 аралығында кофеге −30%'],
            ['title_ru' => 'Бизнес-ланч', 'title_kk' => 'Бизнес-ланч', 'subtitle_ru' => 'Суп, горячее и напиток за 2 490 ₸', 'subtitle_kk' => 'Сорпа, ыстық тағам және сусын 2 490 ₸'],
            ['title_ru' => 'День рождения', 'title_kk' => 'Туған күн', 'subtitle_ru' => 'Десерт в подарок имениннику', 'subtitle_kk' => 'Туған күн иесіне десерт сыйға'],
        ];

        foreach ($promotions as $i => $promo) {
            Promotion::query()->updateOrCreate(
                ['title_ru' => $promo['title_ru']],
                [
                    'title_kk' => $promo['title_kk'],
                    'subtitle_ru' => $promo['subtitle_ru'],
                    'subtitle_kk' => $promo['subtitle_kk'],
                    'image_path' => null,
                    'sort' => $i,
                    'is_visible' => true,
                ],
            );
        }
    }

    /** Curated rails ("Рекомендации", "Летнее предложение") over existing dishes. */
    private function seedCollections(): void
    {
        $collections = [
            ['name_ru' => 'Рекомендуем', 'name_kk' => 'Ұсынамыз', 'slugs' => ['beshbarmak', 'steyk', 'cezar', 'medovik']],
        ];

        foreach ($collections as $i => $data) {
            $collection = Collection::query()->updateOrCreate(
                ['name_ru' => $data['name_ru']],
                [
                    'name_kk' => $data['name_kk'],
                    'sort' => $i,
                    'is_visible' => true,
                ],
            );

            $dishIds = Dish::query()
                ->whereIn('slug', $data['slugs'])
                ->pluck('id', 'slug');

            // Preserve the listed order as the pivot sort.
            $sync = [];
            foreach ($data['slugs'] as $pos => $slug) {
                if (isset($dishIds[$slug])) {
                    $sync[$dishIds[$slug]] = ['sort' => $pos];
                }
            }

            $collection->dishes()->sync($sync);
        }
    }

    /**
     * The sample menu. A category carries either `dishes` (flat) or `children`
     * (subcategories, each with their own `dishes`) — the two-level taxonomy the
     * guest page renders as top tabs + subcategory chips. "Кухня" shows the
     * nested case; the rest stay flat.
     *
     * @return array<int,array<string,mixed>>
     */
    private function menu(): array
    {
        return [
            [
                'name_ru' => 'Завтраки', 'name_kk' => 'Таңғы астар',
                'dishes' => [
                    ['slug' => 'kazhe', 'name_ru' => 'Каша овсяная с ягодами', 'name_kk' => 'Жидекті сұлы ботқасы', 'description_ru' => 'На молоке или воде, свежие ягоды и мёд', 'description_kk' => 'Сүтке немесе суға, балғын жидек пен бал', 'price' => 129000],
                    ['slug' => 'syrniki', 'name_ru' => 'Сырники со сметаной', 'name_kk' => 'Қаймақпен сырниктер', 'description_ru' => 'Творожные, с домашним вареньем', 'description_kk' => 'Сүзбеден, үй тосабымен', 'price' => 159000],
                    ['slug' => 'omlet', 'name_ru' => 'Омлет с овощами', 'name_kk' => 'Көкөністі омлет', 'description_ru' => 'Три яйца, помидоры, зелень', 'description_kk' => 'Үш жұмыртқа, қызанақ, көк', 'price' => 149000],
                ],
            ],
            [
                'name_ru' => 'Кухня', 'name_kk' => 'Асхана',
                'children' => [
                    [
                        'name_ru' => 'Салаты', 'name_kk' => 'Салаттар', 'icon' => 'salad',
                        'dishes' => [
                            ['slug' => 'cezar', 'name_ru' => 'Цезарь с курицей', 'name_kk' => 'Тауық етті Цезарь', 'description_ru' => 'Романо, пармезан, сухарики, соус', 'description_kk' => 'Романо, пармезан, кептірілген нан, тұздық', 'price' => 219000],
                            ['slug' => 'grecheskiy', 'name_ru' => 'Греческий', 'name_kk' => 'Грек салаты', 'description_ru' => 'Овощи, фета, оливки, оливковое масло', 'description_kk' => 'Көкөніс, фета, зәйтүн, зәйтүн майы', 'price' => 189000],
                            ['slug' => 'olivie', 'name_ru' => 'Оливье', 'name_kk' => 'Оливье', 'description_ru' => 'Классический, с говядиной', 'description_kk' => 'Классикалық, сиыр етімен', 'price' => 169000],
                        ],
                    ],
                    [
                        'name_ru' => 'Национальная кухня', 'name_kk' => 'Ұлттық асхана', 'icon' => 'meat',
                        'dishes' => [
                            ['slug' => 'beshbarmak', 'name_ru' => 'Бешбармак', 'name_kk' => 'Бешбармақ', 'description_ru' => 'Конина, тесто, лук, сорпа', 'description_kk' => 'Жылқы еті, қамыр, пияз, сорпа', 'price' => 390000],
                            ['slug' => 'manty', 'name_ru' => 'Манты (5 шт)', 'name_kk' => 'Мәнті (5 дана)', 'description_ru' => 'С мясом и тыквой, на пару', 'description_kk' => 'Ет пен асқабақпен, буға пісірілген', 'price' => 240000],
                            ['slug' => 'kuyrdak', 'name_ru' => 'Куырдак', 'name_kk' => 'Қуырдақ', 'description_ru' => 'Жаркое из баранины с картофелем', 'description_kk' => 'Картоппен қой етінің қуырдағы', 'price' => 320000],
                        ],
                    ],
                    [
                        'name_ru' => 'Горячее', 'name_kk' => 'Ыстық тағамдар', 'icon' => 'flame',
                        'dishes' => [
                            ['slug' => 'dorado', 'name_ru' => 'Дорадо на гриле', 'name_kk' => 'Грильдегі дорадо', 'description_ru' => 'Целая рыба, лимон, травы', 'description_kk' => 'Тұтас балық, лимон, шөптер', 'price' => 450000, 'is_available' => false],
                            ['slug' => 'steyk', 'name_ru' => 'Стейк рибай', 'name_kk' => 'Рибай стейк', 'description_ru' => 'Мраморная говядина, соус на выбор', 'description_kk' => 'Мәрмәр сиыр еті, таңдау бойынша тұздық', 'price' => 690000],
                            ['slug' => 'plov', 'name_ru' => 'Плов', 'name_kk' => 'Палау', 'description_ru' => 'С бараниной, узбекский рецепт', 'description_kk' => 'Қой етімен, өзбек рецепті', 'price' => 280000],
                        ],
                    ],
                ],
            ],
            [
                'name_ru' => 'Десерты', 'name_kk' => 'Десерттер',
                'dishes' => [
                    ['slug' => 'medovik', 'name_ru' => 'Медовик', 'name_kk' => 'Медовик', 'description_ru' => 'Домашний, со сметанным кремом', 'description_kk' => 'Үй, қаймақ кремімен', 'price' => 149000],
                    ['slug' => 'chizkeyk', 'name_ru' => 'Чизкейк', 'name_kk' => 'Чизкейк', 'description_ru' => 'Нью-Йорк, с ягодным соусом', 'description_kk' => 'Нью-Йорк, жидек тұздығымен', 'price' => 169000],
                ],
            ],
            [
                'name_ru' => 'Напитки', 'name_kk' => 'Сусындар',
                'dishes' => [
                    ['slug' => 'chay', 'name_ru' => 'Чай в чайнике', 'name_kk' => 'Шәйнектегі шай', 'description_ru' => 'Чёрный или зелёный, 800 мл', 'description_kk' => 'Қара немесе жасыл, 800 мл', 'price' => 90000],
                    ['slug' => 'ayran', 'name_ru' => 'Айран', 'name_kk' => 'Айран', 'description_ru' => 'Домашний, 300 мл', 'description_kk' => 'Үй, 300 мл', 'price' => 60000],
                    ['slug' => 'kofe', 'name_ru' => 'Капучино', 'name_kk' => 'Капучино', 'description_ru' => 'Двойной эспрессо, молоко', 'description_kk' => 'Қос эспрессо, сүт', 'price' => 120000],
                ],
            ],
        ];
    }
}

# Demo — одноклиентское QR-меню (Дастархан / qmenu.kz)

> **Правило для этого файла:** он обновляется каждый раз, когда в проекте
> появляется что-то новое — таблица, страница админки, роут, команда,
> зависимость или найденные грабли. Внизу — «Журнал изменений», новые записи
> добавляются сверху. Читать этот файл первым, до кода.
>
> ⚠️ **Это НЕ основной SaaS.** Родительский `qr-menu/CLAUDE.md` описывает
> мультитенантный сервис (Laravel API `backend/` + Next.js `front/`). Здесь —
> **отдельное самостоятельное Laravel-приложение** для **одного** заведения.
> Общего кода с SaaS нет: не искать тут `tenant_id`, Sanctum, Next.js, React.
> Пересечение только идейное (двуязычие, тиыны, темы/раскладки, Telegram).
>
> Секреты, адреса сервера, пароли и учётки — в **`../demo-server-notes.md`**
> (лежит уровнем выше, вне git). В этот файл их не переносить.

---

## 1. Что это за проект

Готовое **меню одного заведения** «под ключ» — то, что в основном проекте
продаётся как тариф **Премиум**: меню разворачивается отдельным сайтом, а не
внутри конструктора SaaS. Живёт на `https://qmenu.kz` (домен освободился после
переезда основного проекта на `qr-menu.kz`) и стоит ссылкой в премиум-карточке
лендинга (`PREMIUM_DEMO_URL` в `front/src/content/landing.ts`).

Что умеет:

- гостевое меню на весь экран (обложка, промо-баннеры, подборки, разделы и
  подразделы, поиск, тёмная тема, RU/KZ);
- **приём заказов со стола на сервере** (в SaaS этого ещё нет — там заказ
  локальный в `localStorage`), уведомление персоналу в Telegram;
- **отдельная SEO-страница на каждое блюдо** `/d/{slug}` с canonical и
  schema.org `MenuItem` — главный аргумент Премиума;
- админка Filament на `/admin`: заказы, меню, промо, подборки, настройки
  заведения, SEO.

Ключевые требования домена (те же, что в SaaS):

- **Двуязычность:** `*_ru` / `*_kk` у всех текстов. Казахское поле nullable —
  пустое падает на русское.
- **Деньги в тиынах** (`int`, 1/100 ₸). Никаких float.
- **Мобайл-фёрст**: гость открывает это с телефона по QR со стола.

## 2. Чем принципиально отличается от основного проекта

| | SaaS (`backend/` + `front/`) | Этот `demo/` |
|---|---|---|
| Арендаторы | много, скоуп по `establishment_id` | **один**, `venue_settings` строка `id=1` |
| Фронт | Next.js 16 + React 19, отдельный деплой на Vercel | **Blade + Alpine.js 3** в том же Laravel |
| Авторизация | Sanctum SPA (куки), `is_admin` | обычная сессия `web`, **любой вошедший = владелец** |
| Заказы | локальный чек в браузере гостя | **пишутся в БД**, статусы, Telegram |
| SEO блюда | нет | **есть**, `/d/{slug}` + JSON-LD |
| Кэш меню | `PublicMenu`, сутки, инвалидация событиями | **кэша нет** — Blade рендерит живой Eloquent |
| Тесты | Pest, ~190 зелёных | **нет** (только дефолтные `ExampleTest`) |
| Деплой | GitHub → Vercel / `deploy.sh` | **bare-репозиторий на самом сервере**, push по SSH |

---

## 3. Стек

| Слой | Технология |
|------|-----------|
| Бэкенд | Laravel 13 (`^13.17`), PHP 8.3 |
| Админка | Filament v4 (`^4.0`), Blade/Livewire, сессия `web` |
| БД | MySQL (`demo` локально, `qr_demo` на сервере) |
| Кэш / сессии / очереди | `database` |
| Гостевой фронт | Blade + **Alpine.js 3**, сборка Vite 8 |
| Стили | Tailwind CSS v4 (CSS-first, `@theme inline` — **без** `tailwind.config.js`) |
| Шрифт | Inter через `bunny()` (плагин `laravel-vite-plugin`) |
| Картинки | GD → **WebP** (`App\Support\ImageOptimizer`) |
| Тесты | PHPUnit `^12.5` **не используется** — реальных тестов нет |
| Dev | `laravel/pail`, `laravel/pao`, `pint`, `collision` |

---

## 4. Структура

```
demo/
├─ CLAUDE.md              ← этот файл
├─ AGENTS.md              ← указатель на CLAUDE.md
├─ deploy.sh              ← деплой на сервере (git reset --hard + кэши)
├─ public/build/          ← ⚠️ СОБРАННЫЕ ассеты Vite, ЛЕЖАТ В GIT (см. §12)
├─ routes/web.php         ← 3 роута: меню, блюдо, заказ
├─ config/
│  ├─ menu.php            ← 8 тем · 3 раскладки · 25 иконок категорий
│  ├─ guest.php           ← ВСЕ строки гостевого UI (ru + kk)
│  └─ services.php        ← блок telegram
├─ app/
│  ├─ Models/             ← VenueSetting · MenuCategory · Dish · Collection
│  │                        Promotion · Order · OrderItem · User
│  ├─ Http/Controllers/   ← MenuController · OrderController
│  ├─ Http/Requests/      ← StoreOrderRequest
│  ├─ Support/            ← Money · ImageOptimizer · TelegramNotifier
│  ├─ Console/Commands/   ← demo:dish-photos · demo:promo-photos
│  ├─ Providers/Filament/AdminPanelProvider.php
│  └─ Filament/
│     ├─ Pages/VenueSettings.php        ← «Заведение» (синглтон, 5 вкладок)
│     └─ Resources/                     ← Orders · MenuCategories · Dishes
│                                          Collections · Promotions
├─ resources/
│  ├─ css/app.css         ← токены, тёмная тема, .dish/.promo/.rail
│  ├─ js/app.js           ← весь Alpine-компонент menu() + x-drag-scroll
│  └─ views/
│     ├─ layouts/guest.blade.php        ← <html>, meta, OG, Twitter, @vite
│     └─ menu/
│        ├─ home.blade.php              ← главная (≈400 строк)
│        ├─ dish.blade.php              ← SEO-страница блюда + JSON-LD
│        ├─ icons/*.blade.php           ← инлайновые SVG
│        └─ partials/
│           ├─ dish.blade.php           ← карточка блюда (все 3 раскладки)
│           ├─ cat-icon.blade.php       ← @switch по ключу иконки
│           ├─ layout-preview.blade.php
│           └─ sheet-cart · sheet-settings · sheet-waiter
└─ database/
   ├─ migrations/2026_01_01_00XX_*      ← 8 стартовых таблиц
   │  + 2026_09_01_000100_add_seo_og_to_venue_settings
   │  + 2026_09_01_000200_add_icon_to_menu_categories
   └─ seeders/DatabaseSeeder.php → DemoSeeder.php   ← контент «Дастархан»
```

---

## 5. Модель данных

**`venue_settings` — синглтон заведения (строка `id = 1`).**
`VenueSetting::current()` = `firstOrCreate(['id' => 1])`. Поля: `name`,
`currency`, `default_locale`, `description_ru/kk`, `address`, `phone`,
`wifi_ssid`, `wifi_password`, `instagram_url`/`facebook_url`/`tiktok_url`,
`theme`, `layout`, `cover_path`, `logo_path`, `show_logo`, `ordering_enabled`,
`tables_count`, `seo_title_ru/kk`, `seo_description_ru/kk`, `seo_keywords_ru/kk`,
`seo_og_path`. Хелперы: `coverUrl()` (падает на `DEFAULT_COVER_URL` — фото
Unsplash), `seoTitle()/seoDescription()/seoKeywords()/ogImageUrl()` — резолвят
по `default_locale` с откатом на `ru`, потом на `name`/`description`, потом на
обложку.

**`menu_categories` — два уровня через самоссылку.** `parent_id` → сама себя
(`nullOnDelete`). `parent_id = null` — верхний уровень (вкладка), иначе —
подкатегория (чип). Третий уровень **не поддержан намеренно**: форма предлагает
в родители только верхние категории и никогда саму запись. Поля: `name_ru/kk`,
`icon` (ключ из `config('menu.category_icons')`, nullable), `sort`, `is_visible`.

**`dishes`** — `menu_category_id` (может указывать и на верхнюю категорию, и на
подкатегорию), `name_ru/kk`, `description_ru/kk`, **`price` `unsignedBigInteger`
в тиынах**, `image_path`, `slug` (**unique**, минтится автоматически), `sort`,
`is_available` (стоп-лист: блюдо **видно**, но с меткой «Закончилось»),
`is_visible` (скрыто от гостя совсем).

**`collections` + `collection_dish`** — «подборки» (горизонтальные ленты вверху
меню, напр. «Рекомендуем»). Пивот с unique-парой; блюдо может лежать в
нескольких подборках независимо от своего раздела.

**`promotions`** — промо-баннеры («сторис» горизонтальной лентой):
`title_ru/kk`, `subtitle_ru/kk`, `image_path`, `sort`, `is_visible`.

**`orders` / `order_items`** — заказ: `table_number` (nullable), `comment`,
`total` (тиыны), `status` из `Order::STATUSES`
(`new` · `accepted` · `ready` · `done` · `cancelled`, русские подписи прямо в
константе), индекс `[status, created_at]`. `ACTIVE_STATUSES = [new, accepted,
ready]`. Позиция несёт **снимок** `name_ru`, `name_kk`, `price`, `quantity` и
мягкую ссылку `dish_id` (`nullOnDelete`) — счёт **никогда** не читает живое
блюдо, иначе вчерашний заказ переписался бы сегодняшней ценой.

**`users`** — один аккаунт владельца. `#[Fillable(['name','email','password'])]`,
`canAccessPanel(): true`.

---

## 6. Роуты

```php
Route::get('/',           [MenuController::class, 'home'])->name('menu.home');
Route::get('/d/{slug}',   [MenuController::class, 'dish'])->name('menu.dish');
Route::post('/orders',    [OrderController::class, 'store'])->name('orders.store');
```

Плюс `/admin/*` — панель Filament (её роуты регистрирует `AdminPanelProvider`).
Больше публичной поверхности нет. API-роутов нет вообще.

`MenuController@home` собирает дерево одним eager-load
(`children`, `dishes`, `children.dishes`, всё отфильтровано по `is_visible`),
затем **выбрасывает пустые подкатегории и пустые группы** — иначе в меню висели
бы заголовки без блюд. Так же выкидываются подборки без единого видимого блюда.

`MenuController@dish` — `where('slug', …)->where('is_visible', true)->firstOrFail()`
плюс до 6 «похожих» блюд из того же раздела.

---

## 7. Гостевое меню

**Одна страница, всё состояние в Alpine.** `home.blade.php` собирает `$boot`
(валюта, дефолтные локаль/тема/раскладка, справочник тем, плоская карта блюд
`{id: {name, price, …}}`, `copy` = `config('guest')`, `ordering`, `tablesCount`,
`orderUrl`) и отдаёт его в `x-data="menu(@js($boot))"`. Компонент — в
`resources/js/app.js` (≈460 строк).

Что делает клиент:

- **Локаль RU/KZ, тема (8 цветов), раскладка (3 варианта), тёмная тема** —
  выбирает **сам гость**, сохраняется в `localStorage` (`qmenu.guest-prefs.v1`).
  Настройки владельца в админке — только **значение по умолчанию**.
- **Раскладка** ставится атрибутом `:data-layout` на корне; вся разница между
  `classic` / `grid` / `compact` — в CSS (`.dish` + `[data-layout='…']`),
  разметка карточки **одна** (`partials/dish.blade.php`).
- **Тема** — CSS-переменные `--accent*` на корневом элементе, дочерние
  `bg-accent`/`text-accent` наследуют каскадом.
- **Поиск** — чисто клиентский, по атрибуту `data-key` на карточке (ru+kk
  название и описание в нижнем регистре); секция прячется, если в ней нет
  совпадений (`sectionHasMatch`).
- **Корзина** — `localStorage` (`qmenu.guest-cart.v1`), `?table=N` из URL
  подставляет номер стола.
- Три нижние шторки: **Корзина**, **Официант** (локальная заглушка, ничего не
  шлёт), **Настройки**.

---

## 8. Админка (Filament, `/admin`)

`AdminPanelProvider`: `->default()->id('admin')->path('admin')->login()`,
`brandName('Меню · Админ')`, `Color::Amber`, автодискавери Resources/Pages/Widgets,
виджет `AccountWidget`.

| Раздел | Что умеет |
|---|---|
| **Заказы** (`sort 1`) | таблица с `->poll('15s')` (висит на экране в кухне), фильтр по статусу, **бейдж с числом новых** в сайдбаре (красный), одно-тапные действия «Принять / Готов / Выдан / Отменить» прямо из списка, просмотр состава (infolist по снимкам позиций). Create/Edit нет — заказы создаёт гость |
| **Категории меню** | родитель (только верхние), `name_ru/kk`, **«Значок»** (Select по `config('menu.category_icons')`, в выпадашке эмодзи-подсказка), `sort`, `is_visible` |
| **Блюда** (`sort 3`) | раздел выбором по дереву («Кухня → Салаты»), двуязычные поля, **цена в тенге ↔ тиыны на границе формы** (`formatStateUsing` / `dehydrateStateUsing`), slug (пусто = сгенерируется), фото (квадрат, WebP), тумблеры наличия/видимости, `->reorderable('sort')` |
| **Подборки** | набор блюд через `Select::multiple()->relationship('dishes')` — пивот пишет Filament |
| **Промо** | заголовок/подпись RU+KK, широкая картинка (WebP), порядок, видимость |
| **Заведение** (`VenueSettings`, страница-синглтон) | вкладки **Основное · Оформление · Контакты · Заказы · SEO**. `mount()` заполняется из `VenueSetting::current()`, `save()` делает `update($data)` + Notification |

Доступ: **любой вошедший пользователь** (сайт одноклиентский). Флага `is_admin`,
как в SaaS, здесь нет — но `User` **обязан** реализовывать `FilamentUser`
(см. граблю в §11).

---

## 9. Заказы: где проходит граница доверия

`OrderController@store` — единственная точка записи от гостя. Правила:

1. `abort_unless($venue->ordering_enabled, 404)` — выключенный приём заказов
   не обходится подделкой запроса.
2. `StoreOrderRequest` валидирует **только форму** (`items` 1..100,
   `items.*.id` `exists:dishes,id`, `qty` 1..99, `table_number` 1..9999,
   `comment` ≤ 500, `locale` in `ru,kk`).
3. Блюда **перечитываются из БД** с фильтром `is_visible` + `is_available`;
   если чего-то нет — 422 «Некоторые блюда уже недоступны. Обновите страницу.».
4. **Сумма считается на сервере.** Присланный клиентом `total` не читается
   вообще. Позиции пишутся снимками (`name_ru/name_kk/price`).
5. Всё в `DB::transaction`, затем **best-effort** `TelegramNotifier::notifyNewOrder`.
6. Ответ — `201` с `{id, number, status}`.

`TelegramNotifier`: `isConfigured()` (нет токена/чата → **молчаливый no-op**,
локально ничего наружу не уходит), `Http::timeout(8)`, `try/catch` +
`Log::warning` — **никогда** не роняет оформление заказа.

---

## 10. Картинки и SEO

**Каждая загрузка пережимается в WebP через GD** — `App\Support\ImageOptimizer`
(`imagecreatefromstring` → `imagecopyresampled` → `imagewebp`), два режима:
`MODE_CONTAIN` (вписать в длинную сторону) и `MODE_SQUARE` (центр-кроп).
Апскейла нет, альфа сохраняется, имя файла случайное (новый URL сам сбрасывает
кэш браузера/CDN), диск `public`. Подключается в Filament через
`FileUpload::saveUploadedFileUsing()`:

| Что | Режим | Размер / качество |
|---|---|---|
| Фото блюда | `MODE_SQUARE` | 800px / q80 |
| Промо-баннер | `MODE_CONTAIN` | 1200px / q80 |
| Обложка заведения | `MODE_CONTAIN` | 1600px / q82 |
| Логотип | `MODE_CONTAIN` | 512px / q82 |
| OG-картинка | `MODE_CONTAIN` | 1200px / q82 |

Нужен `php8.3-gd` **с поддержкой WebP** и симлинк `public/storage`
(`artisan storage:link`, есть в `deploy.sh`).

**SEO.** `layouts/guest.blade.php` печатает `<title>`, description, keywords,
полный набор OG и Twitter-тегов из `VenueSetting` (правится в `/admin` →
«Заведение» → «SEO»). Страница блюда `/d/{slug}` перекрывает title/OG своими,
ставит `canonical`, `og:type=product` и **JSON-LD `MenuItem`** с `Offer`
(цена в тенге строкой, `priceCurrency`, `availability` In/OutOfStock).
Ключевые слова наследует от заведения.

---

## 11. Запуск и конвенции

```bash
composer setup     # установка + ключ + миграции + сиды (см. composer.json)
php artisan dev    # сервер + очередь + логи + vite одной командой
npm run dev        # только фронт-сборка в watch
npm run build      # ⚠️ обязательно перед деплоем, см. §12
./vendor/bin/pint  # стиль PHP
```

Локальный `.env`: `DB_CONNECTION=mysql`, `DB_DATABASE=demo`, `root`/`root`,
`APP_URL=http://localhost:8000`, session/cache/queue = `database`.

**Конвенции** (те же, что в SaaS):

- Код и комментарии — **на английском**, UI — русский + казахский.
- Комментарий объясняет *почему*, а не *что*.
- Строки гостевого UI — **только** в `config/guest.php` (ru + kk). Добавил
  ключ — добавь в **обе** локали, иначе `t()` вернёт пустоту.
- Темы/раскладки/иконки — **только** в `config/menu.php`.
- Деньги — целые тиыны; форматирование **одно** — `App\Support\Money::format()`.
- Eloquent — всегда `with()`, никаких N+1.
- Мобайл-фёрст, `prefers-reduced-motion` уважаем.

---

## 12. ⚠️ Грабли (проверено на практике)

### ⚠️ На сервере НЕТ Node — `public/build` коммитится в git

`npm`/`node` на сервере не установлены, `deploy.sh` ассеты **не собирает**.
Папка `public/build` принудительно добавлена в репозиторий. Любая правка
`resources/js` или `resources/css`:

```bash
npm run build && git add -f public/build && git commit -m "rebuild assets"
```

Забудешь — на проде останется старый бандл, и правка просто не появится.

### ⚠️ Filament в production пускает только `FilamentUser`

При `APP_ENV=production` Filament требует, чтобы модель реализовывала
`Filament\Models\Contracts\FilamentUser`. Локально пускало и без него, а на
домене вход после **верного** пароля отдавал **403**. Поэтому
`User implements FilamentUser` с `canAccessPanel(): true` (сайт одноклиентский —
любой вошедший = владелец). **Не удалять этот метод.**

### ⚠️ В Alpine-компоненте строки лежат в `strings`, а не в `copy`

У компонента есть метод `copy(text)` (копирование пароля Wi-Fi в буфер). Если
назвать поле со строками `copy`, метод его затрёт и `t()` начнёт падать.
Поэтому `strings: boot.copy`. Не «упрощать» переименованием.

### ⚠️ Боковые отступы горизонтальных лент — на краевых детях, не на контейнере

Часть мобильных движков **не рисует trailing inline-padding** у скролл-контейнера:
последняя карточка липла к краю. Отступы в `.promo-scroll` / `.rail` заданы
через `> :first-child { margin-left }` / `> :last-child { margin-right }` —
margin у флекс-элементов рисуется всегда. Не возвращать на `px-4` контейнера.

### ⚠️ Пустота под короткой категорией — «обзор» прячется + группа прижимается к низу

Клик по вкладке пиннит заголовок группы под липкую шапку; у короткой группы
(3 блюда) контента не хватает заполнить экран → снизу зияла дыра ~131px.
Лечится двумя вещами:

- блоки «обзора» (о заведении, промо-сторис, подборки) лежат **над** липкой
  шапкой и скрыты при фильтре: `x-show="!searching && activeTop === 0"`;
- `selectTop(id)` скроллит на `max(0, min(pinTop, bottomAlign))` с
  `dockOffset: 72` — **высокая** группа пиннится как раньше, **короткая**
  прижимается низом к нижнему доку.

Перед замером высот обязателен `$nextTick` — иначе `x-show` ещё не применился.
`goToSub` (клик по подкатегории) намеренно оставлен на чистом пиннинге.

### ⚠️ Хвост под последней секцией держится scroll-spy, а не распоркой

Раньше внизу стоял `min-h-[70vh]`-заполнитель, чтобы последняя вкладка могла
подсветиться. Убран: `setupScrollSpy()` **пиннит последнюю вкладку у низа
страницы** (`innerHeight + scrollY >= scrollHeight - 4`). Не возвращать распорку.

### ⚠️ `x-drag-scroll` — только мышь

Ленты (`overflow-x` + скрытый скроллбар) не листались обычной мышью на ноутбуке.
Кастомная директива `Alpine.directive('drag-scroll', …)` даёт grab-drag и
колесо→горизонталь, но **только при `pointerType === 'mouse'`** — тач и трекпад
оставлены на нативном скролле, иначе конфликт. Драг дальше порога **глотает
следующий клик**, иначе карточка-ссылка открывалась бы после перетаскивания.

### ⚠️ Набор иконок в конфиге и `cat-icon.blade.php` должны совпадать

Ключи живут в `config('menu.category_icons')` (25 шт.), сами SVG — в
`resources/views/menu/partials/cat-icon.blade.php` (`@switch`). Неизвестный или
пустой ключ рендерит **ничего** — намеренно, чтобы устаревший ключ не навязал
чужой глиф. Добавляешь иконку — правь **оба** места. Иконки показываются
**только на чипах подкатегорий** (решение владельца), верхние вкладки без них.

### ⚠️ Кэша меню здесь нет

В отличие от SaaS (`PublicMenu`, сутки + инвалидация событиями), это приложение
рендерит живой Eloquent на каждый запрос. Значит, правка в админке видна сразу —
но и любой тяжёлый запрос бьёт по каждому гостю. Все выборки уже с `with()`;
добавляя связь на главную, проверяй, что она попала в eager-load.

### ⚠️ Позиции заказа — снимки, живое блюдо не читать

`OrderItem` хранит `name_ru/name_kk/price` на момент заказа. Показывать в счёте
`$item->dish->price` нельзя: `dish_id` — мягкая ссылка (`nullOnDelete`), блюдо
могло подорожать или исчезнуть.

### ⚠️ Цена в форме Filament: тиыны ↔ тенге на границе

`DishForm` делит на 100 в `formatStateUsing` и умножает в `dehydrateStateUsing`.
Уберёшь — цена уедет в ×100. Тот же приём, что в `PlanForm` основного проекта.

### ⚠️ Тестов нет

В `tests/` только дефолтные `ExampleTest`. Любую правку проверять руками
(браузер / `curl`) — на «зелёный прогон» здесь опереться не на что. Для вёрстки —
Playwright ставится разово (`npm i -D playwright && npx playwright install chromium`)
и **в `package.json` не держится**.

### ⚠️ Кириллица в имени пользователя ломает ssh из Git Bash

`C:\Users\Ахмет` манглится, `~/.ssh` не находится. Использовать Windows-ssh
через PowerShell (см. `../demo-server-notes.md`).

### ⚠️ Tinker через ssh — только stdin и без тега `<?php`

`ssh … "php artisan tinker < /tmp/file.php"` работает; форма
`php artisan tinker file.php` уходит в интерактивный REPL и **виснет**.

---

## 13. Деплой

Схема — **bare-репозиторий на самом сервере**, GitHub не используется. Локально
remote `server`; на сервере рабочая копия `/var/www/qr-demo`. Адреса, ключи,
пароли — `../demo-server-notes.md`.

```powershell
# локально, из папки demo/ (PowerShell — из-за кириллицы в пути)
$env:GIT_SSH_COMMAND = 'C:/WINDOWS/System32/OpenSSH/ssh.exe -i C:/Users/Ахмет/.ssh/id_ed25519'
npm run build; git add -f public/build; git commit -m "…"
git push server main
```

```bash
# затем на сервере
ssh root@<ip> "cd /var/www/qr-demo && ./deploy.sh"
```

`deploy.sh`: `git fetch origin main` + `git reset --hard origin/main` →
`composer install --no-dev --optimize-autoloader` → `migrate --force` →
`storage:link` → `filament:assets` → `config:cache route:cache view:cache
filament:optimize` → `chown -R www-data`, `chmod 775 storage bootstrap/cache`,
`chmod 640 .env` → `systemctl reload php8.3-fpm`.

**Что в `deploy.sh` намеренно НЕ входит:**

- сборка ассетов (Node на сервере нет — см. §12);
- `DemoSeeder` (затёр бы контент клиента);
- `demo:dish-photos` / `demo:promo-photos` (стоковые фото — только вручную).

Exec-бит `deploy.sh` закреплён в git (`100755`), иначе `git reset --hard`
возвращал 644 и запуск падал с `Permission denied`. `deploy.sh` в конце делает
`chown www-data`, поэтому на сервере один раз выполнено
`git config --global --add safe.directory /var/www/qr-demo`.

### Ручные команды (не в деплое)

```bash
php artisan demo:dish-photos [--force]    # стоковые фото блюд (LoremFlickr → WebP)
php artisan demo:promo-photos [--force]   # стоковые фото промо-баннеров
php artisan db:seed --class=DemoSeeder    # ⚠️ только на пустой базе
```

### Осталось перед передачей клиенту

- сменить пароль админки `admin@demo.kz`;
- заполнить `TELEGRAM_BOT_TOKEN` / `TELEGRAM_ADMIN_CHAT_ID` в серверном `.env`
  + `php artisan config:cache` (сейчас уведомления спят);
- заменить стоковые фото блюд и баннеров на настоящие через админку;
- сменить засвеченный root-пароль сервера.

---

## 14. Журнал изменений

> Записи до 2026-09-02 восстановлены из git-истории и `../demo-server-notes.md`.

### 2026-09-03

- **Написан этот файл.** До сих пор `demo/CLAUDE.md` и `AGENTS.md` содержали
  только заглушку Laravel Boost («поставьте boost и перечитайте AGENTS.md»),
  а родительский `qr-menu/CLAUDE.md` описывает **другое** приложение — каждая
  сессия заново разбирала проект по исходникам. Теперь инструкция полная:
  отличия от SaaS, схема данных, роуты, гостевой Alpine-слой, админка, заказы,
  картинки, SEO, деплой и все найденные грабли. `AGENTS.md` сведён к указателю
  на этот файл. Код не менялся.

### 2026-09-01 (пустота под короткой категорией) — `68e6ae0`

- Блоки «обзора» скрываются при выбранной категории (`x-show="!searching &&
  activeTop === 0"`), короткая группа прижимается низом к доку
  (`selectTop` → `max(0, min(pinTop, bottomAlign))`, `dockOffset: 72`).
  Проверено Playwright 390×780: «Завтраки» — зазор 131px → 13px, высокая
  «Кухня» пиннится как раньше. Детали — §12.

### 2026-09-01 (иконки категорий) — `59351e7`

- `menu_categories.icon` (миграция `…000200`), Select в форме категории,
  инлайновый SVG на чипах **подкатегорий**. Набор — `config('menu.category_icons')`
  (25 ключей), глифы — `partials/cat-icon.blade.php`. На сервере значения
  забэкфиллены руками через tinker (`DemoSeeder` на деплое не гоняется).
  Пересборка ассетов не потребовалась — классы уже были в бандле.

### 2026-09-01 (мобильные баги меню) — `0979119`

- Боковые отступы лент перенесены на краевых детей; ряд подкатегорий получил
  `pt-2` + верхнюю линию; убран `min-h-[70vh]`-хвост, вместо него scroll-spy
  пиннит последнюю вкладку у низа страницы, `<main>` ужат `pb-44` → `pb-32`.

### 2026-09-01 (drag-scroll + фото промо) — `2390c32`

- Директива `x-drag-scroll` (мышь: grab-drag + колесо→горизонталь) на 4 ленты.
- Команда `demo:promo-photos` — стоковые баннеры вместо оранжевого градиента.

### 2026-09-01 (SEO/OG из админки) — `07e5f08`

- Миграция `…000100`: `seo_keywords_ru/kk`, `seo_og_path` + бэкфилл дефолтов
  «Дастархан» **только в пустые колонки** (правки владельца не затираются).
  Всё правится в `/admin` → «Заведение» → «SEO»; рендерится серверным Blade
  через хелперы `VenueSetting`. Проверено живьём на `https://qmenu.kz/`.
- **Домен и HTTPS:** `qmenu.kz` + `www`, Let's Encrypt, редирект HTTP→HTTPS,
  `APP_URL=https://qmenu.kz`. QR для гостей генерировать на домен, не на IP:8083.

### 2026-09-01 (ранее)

- `demo:dish-photos` — стоковые фото блюд (`987a709`).
- `canAccessPanel()` — починка 403 в панели на проде (`107d82f`).
- Exec-бит `deploy.sh` закреплён в git (`69e1371`).

### Первый деплой — `b9b2687`

- Одноклиентское меню целиком: 8 миграций, модели, `MenuController`/
  `OrderController`, Filament-панель, гостевой Blade+Alpine, `DemoSeeder`
  («Дастархан»: разделы с подразделами, 17 блюд, 3 промо, подборка
  «Рекомендуем»), `deploy.sh`, bare-репозиторий на сервере, порт 8083.
  Проверено живьём: `/` → 200, `/d/beshbarmak` → 200, `/.env` → 403,
  заказ POST → 201 с суммой, посчитанной на сервере. Соседние проекты целы.

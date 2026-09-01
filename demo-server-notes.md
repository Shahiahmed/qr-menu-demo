# Demo (single-client) QR menu на сервере — памятка

> Лежит **вне git-репозитория** (`demo/` — это git, а этот файл уровнем выше),
> поэтому здесь можно держать адреса, учётки и пароли. Секретов в самом
> репозитории `demo/` нет (`.env` в `.gitignore`).

## Координаты

| | |
|---|---|
| Сервер | тот же, что и основной проект: `ssh root@185.194.217.14` (по ключу `~/.ssh/id_ed25519`) |
| Каталог | `/var/www/qr-demo` |
| Bare-репозиторий | `/var/www/qr-demo.git` (деплой пушем по SSH, **не** через GitHub) |
| Домен | **`https://qmenu.kz`** (+ `www.qmenu.kz`) — освободился после переезда основного проекта на `qr-menu.kz` |
| Порт | **8083** (прямой доступ по IP; домен `qmenu.kz` через nginx 80/443) |
| Гостевое меню | `https://qmenu.kz/` (или `http://185.194.217.14:8083/`) |
| Админка (Filament) | `https://qmenu.kz/admin` |
| БД | `qr_demo`, пользователь `qr_demo`@localhost |
| Пароль БД | лежит только в `/var/www/qr-demo/.env` (`DB_PASSWORD`) |
| nginx | `/etc/nginx/sites-available/qr-demo` (порт 8083) + `/etc/nginx/sites-available/qmenu` (домен `qmenu.kz`, 80/443, Certbot) — оба симлинком в `sites-enabled`, один и тот же root `/var/www/qr-demo/public` |
| SSL | Let's Encrypt на `qmenu.kz` + `www.qmenu.kz`, автопродление certbot; DNS: apex A `qmenu.kz` → `185.194.217.14` (PS.kz, ID 682288). `api.qmenu.kz` остаётся на этом же IP — это основной проект, не трогать. |
| Вход в админку | `admin@demo.kz` / `Qmenu-Admin-2026` (сброшен 2026-09-01 — при передаче клиенту сменить на его пароль) |

Панель Filament открыта любому вошедшему пользователю (сайт одноклиентский —
логин один). Отдельного флага `is_admin`, как в основном проекте, здесь нет,
но `User` **обязан** реализовывать `FilamentUser` (`canAccessPanel(): true`) —
см. граблю ниже.

## ⚠️ 403 в админке на проде — нужен `FilamentUser`

Filament в `APP_ENV=production` пускает в панель **только** модель, реализующую
контракт `Filament\Models\Contracts\FilamentUser` (метод `canAccessPanel`).
Локально/по IP:8083 (другое окружение) пускало и без него, а на домене с
`APP_ENV=production` вход после верного пароля отдавал **403 Forbidden**.
Починено: `User implements FilamentUser`, `canAccessPanel()` возвращает `true`
(одноклиентский сайт — любой вошедший = владелец). Не удалять этот метод.

## ⚠️ git-деплой как root: dubious ownership + exec-бит

- `deploy.sh` в конце `chown -R www-data`, поэтому следующий `git` под root
  ругается `dubious ownership`. Один раз выполнено на сервере:
  `git config --global --add safe.directory /var/www/qr-demo`.
- Exec-бит `deploy.sh` закреплён в git (mode 100755) — иначе `git reset --hard`
  восстанавливал 644 и `./deploy.sh` падал с `Permission denied`.

## Как устроен git-деплой

GitHub тут не используется (у основного проекта — `github.com/Shahiahmed/qr-back`,
но для demo отдельный репозиторий не заводили). Схема — **bare-репозиторий на
самом сервере**:

- Локально в `demo/` — обычный git-репозиторий, remote `server` →
  `ssh://root@185.194.217.14/var/www/qr-demo.git`.
- `/var/www/qr-demo` — рабочая копия, клон этого bare-репозитория.

### Обновление кода

Локально (из папки `demo/`, **PowerShell** — из-за кириллицы в имени
пользователя нужен именно Windows-ssh):

```powershell
$env:GIT_SSH_COMMAND = 'C:/WINDOWS/System32/OpenSSH/ssh.exe -i C:/Users/Ахмет/.ssh/id_ed25519'
git push server main
```

Затем на сервере:

```bash
ssh root@185.194.217.14 "cd /var/www/qr-demo && ./deploy.sh"
```

`deploy.sh` делает `git fetch/reset --hard origin/main`, `composer install`,
`migrate --force`, `storage:link`, `filament:assets`, кэши, права,
`reload php8.3-fpm`.

## ⚠️ На сервере НЕТ Node — ассеты Vite коммитятся в репозиторий

`npm`/`node` на сервере не установлены, поэтому `public/build` **принудительно
добавлен в git** (`git add -f public/build`) и приезжает вместе с кодом. При
изменении фронта — пересобрать локально и закоммитить:

```bash
cd demo && npm run build && git add -f public/build && git commit -m "rebuild assets"
```

## ⚠️ Кириллица в имени пользователя ломает git-bash ssh

`ssh` из Git Bash не находит `~/.ssh` (путь `C:\Users\Ахмет` манглится в
кракозябры). Использовать **Windows-ssh** (`C:\WINDOWS\System32\OpenSSH\ssh.exe`)
через PowerShell, либо `GIT_SSH_COMMAND` с прямыми слэшами (см. выше).

## Соседи не тронуты

Отдельные БД (`qr_demo`), отдельный порт (8083), отдельный nginx-блок, отдельный
каталог. Общий только пул `php8.3-fpm` (как у всех проектов на этом сервере —
`reload` на деплое кратко сбрасывает opcache соседям, принятая практика).
Проверено после деплоя: 8080 → 302, 8082 → 200, phytobiotech 443 → 200.

## Проверено живьём (деплой)

- `/` → 200 (меню «Дастархан» рендерится), `/d/beshbarmak` → 200, `/.env` → 403
- `/admin/login` → 200 (снаружи по IP:8083)
- Заказ: POST с CSRF → **201**, сумма считается на сервере (2×390000 = 780000
  тиын). Тестовый заказ удалён — клиент стартует с чистой таблицей заказов.

## SEO / OG — редактируется в админке (2026-09-01)

Всё SEO и OG теперь правится в `/admin` → **«Заведение» → вкладка «SEO»**:
двуязычные Title / Description / Ключевые слова (RU + KK) и **OG-картинка**
для превью при отправке ссылки. Хранится в `venue_settings` (синглтон id=1),
рендерится в `<head>` серверным Blade (`layouts/guest.blade.php`) через
хелперы модели `VenueSetting` (`seoTitle/seoDescription/seoKeywords/ogImageUrl`),
локаль-зависимо по `default_locale`, с откатами (пусто → название/описание;
OG-картинка пусто → обложка → дефолтное фото). Главная берёт дефолты из layout;
страницы блюд (`/d/{slug}`) держат свои Title/OG + JSON-LD и наследуют
ключевые слова заведения.

Дефолтные значения (RU+KK) для «Дастархан» **уже залиты** миграцией
`2026_09_01_000100_add_seo_og_to_venue_settings` (бэкфилл только пустых
колонок — правки владельца не затираются). Миграция накатана на сервере
деплоем — **ручных шагов не осталось**. Проверено живьём: `https://qmenu.kz/`
отдаёт нужные `<title>`, `description`, `keywords`, `og:*`.

## Осталось (follow-up)

- ~~**Домен + HTTPS.**~~ **Сделано (2026-09-01):** `https://qmenu.kz` + `www`,
  APP_URL переключён на `https://qmenu.kz`, HTTP→HTTPS редирект, соседи целы.
  QR-код для гостей теперь генерировать на `https://qmenu.kz`, не на IP:порт.
- **Фото блюд — стоковые заглушки** (залиты командой `php artisan demo:dish-photos`,
  LoremFlickr по тегам, прогнаны через `ImageOptimizer` в WebP/квадрат). Для
  реального заведения заменить своими снимками через админку (Блюда → фото);
  `demo:dish-photos --force` перезальёт стоковые заново.
- **Сменить пароль админки** `admin@demo.kz` и передать клиенту.
- **Сменить засвеченный root-пароль сервера** (та же задача, что у основного
  проекта, — пароль был в переписке; вход по ключу уже настроен).
- Telegram-уведомления о заказах спят: заполнить `TELEGRAM_BOT_TOKEN` /
  `TELEGRAM_ADMIN_CHAT_ID` в `/var/www/qr-demo/.env` + `php artisan config:cache`.

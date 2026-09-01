#!/usr/bin/env bash
#
# Deploys the single-client demo menu in place. Run on the server, from the
# project root: `cd /var/www/qr-demo && ./deploy.sh`.
#
# Safe to re-run: it resets to origin/main, so a half-finished manual edit on
# the server is discarded rather than merged.
#
# NOTE: the server has no Node toolchain, so Vite assets are NOT built here —
# they are committed to the repo (public/build) and arrive with `git reset`.
# Rebuild them locally (`npm run build`) and commit when front-end assets change.

set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

# Composer refuses to run as root without this, and the server has no other user.
export COMPOSER_ALLOW_SUPERUSER=1

echo "→ code"
git fetch origin main
git reset --hard origin/main

echo "→ dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

echo "→ database"
# The schema belongs to this project, so migrating on deploy is correct here.
# The demo content seeder is NOT run here — it is a one-time manual step.
php artisan migrate --force

echo "→ storage link"
# public/storage → storage/app/public, so uploaded images are servable.
# Idempotent; already-linked is fine, so don't let it trip `set -e`.
php artisan storage:link || true

echo "→ admin panel assets"
# Filament serves compiled CSS/JS from public/. Republish on every deploy so
# the panel doesn't 404 its own assets after a Filament upgrade.
php artisan filament:assets

echo "→ caches"
php artisan config:cache
php artisan route:cache
php artisan view:cache
# The guest site is Blade; filament:optimize caches the panel's components too.
php artisan filament:optimize

echo "→ permissions"
chown -R www-data:www-data "$PROJECT_DIR"
chmod -R 775 storage bootstrap/cache
chmod 640 .env

echo "→ opcache"
# Without this the old bytecode keeps serving after the files change.
systemctl reload php8.3-fpm

echo "✓ deployed"

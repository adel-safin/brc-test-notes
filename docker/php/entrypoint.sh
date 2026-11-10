#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ]; then
  touch .env
fi

if [ -z "${APP_KEY:-}" ]; then
  if [ ! -s storage/app.key ]; then
    php -r 'echo "base64:".base64_encode(random_bytes(32));' > storage/app.key
  fi
  export APP_KEY="$(cat storage/app.key)"
fi

i=0
until php -r '
  $port = getenv("DB_PORT") ?: "3306";
  try {
    new PDO(
      sprintf("mysql:host=%s;port=%s;dbname=%s", getenv("DB_HOST"), $port, getenv("DB_DATABASE")),
      getenv("DB_USERNAME"),
      getenv("DB_PASSWORD")
    );
  } catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage()."\n");
    exit(1);
  }
'; do
  i=$((i + 1))
  if [ "$i" -gt 30 ]; then
    echo "database is not ready" >&2
    exit 1
  fi
  sleep 1
done

php artisan migrate --force
chown -R www-data:www-data storage bootstrap/cache

exec php-fpm

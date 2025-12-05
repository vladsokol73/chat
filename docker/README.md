# Docker окружение для gchat

Этот каталог содержит конфигурацию Docker Compose для локального запуска всего проекта: PHP/Laravel, Nginx, Next.js, Postgres и Redis.

## Подготовка переменных окружения

- Копируйте пример переменных окружения Docker Compose:

```bash
cp docker/.env.example docker/.env
```

- Убедитесь, что в корне проекта (не в папке `docker/`) создан `.env` для Laravel. Если файла нет, создайте из примера:

```bash
cp .env.example .env
```

- При необходимости отредактируйте значения в `docker/env/app.env` и `docker/env/next.env` (используются сервисами `app` и `next` соответственно через `env_file`).

Примечание: Скрипт `docker/php/entrypoint.sh` больше НЕ копирует автоматически `.env.example` в `.env`. Создайте корневой `.env` заранее, иначе генерация `APP_KEY` и миграции будут пропущены.

## Как это работает

- `app` — PHP-FPM контейнер с Laravel. Делает:
  - `composer install` при старте (если есть `composer.json`)
  - `php artisan key:generate` (если `APP_KEY` пуст и есть `.env`)
  - `php artisan migrate --force`
  - `php artisan l5-swagger:generate`
  - запускает `supervisord` (очереди/вебсокеты и т.п.)
- `nginx` — проксирует HTTP на `app`, читает конфиг `docker/nginx/default.conf`.
- `next` — dev-сервер фронтенда из каталога `next/`.
- `postgres` — БД PostgreSQL, данные сохраняются в volume `postgres_data`.
- `redis` — кеш/очереди, данные сохраняются в volume `redis_data`.

## Порты

- Nginx: http://localhost:8080
- Next.js: http://localhost:3000
- Laravel Echo Server / websockets (если используется): 6001 (проброшен из `app`)

## Запуск

Из корня репозитория:

```bash
cd docker
# Первый запуск (или при изменении Dockerfile):
docker compose up -d --build

# Последующие запуски:
docker compose up -d
```

Проверить логи сервиса (пример для `app`):

```bash
docker compose logs -f app
```

Войти в контейнер `app`:

```bash
docker compose exec app sh
```

## Остановка и очистка

```bash
# Остановка контейнеров:
docker compose down

# Полная очистка с удалением томов (данные Postgres/Redis будут удалены):
docker compose down -v
```

## Полезное

- Если вы изменили `.env` (корневой) и хотите заново сгенерировать ключ:

```bash
docker compose exec app php artisan key:generate --force
```

- Если при старте не произошли миграции, запустите вручную:

```bash
docker compose exec app php artisan migrate --force
```

- Если меняете зависимости PHP:

```bash
docker compose exec app composer install --no-interaction --prefer-dist
```


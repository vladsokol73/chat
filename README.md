## Chat Service (gchat)

Backend‑ и frontend‑часть чата для ERP‑платформы.

### Архитектура

- **Laravel backend** (корень проекта) — REST API, веб‑интерфейс, WebSockets, интеграции.
- **Next.js frontend** (`next/`) — современный SPA/SSR‑интерфейс оператора.
- **Docker окружение** (`docker/`) — готовые конфиги для локального запуска всего стека (Laravel, Nginx, Next.js, Postgres, Redis).

### Основной функционал

- Многоканальный чат с клиентами.
- Воронки/фаннелы, интеграции с внешними сервисами.
- Поддержка AI‑функций (например, через Dify/OpenAI, см. соответствующие сервисы).
- Статистика, мониторинг, события (см. `app/Events`, `app/Services`, `app/DTO`).

### Запуск backend (Laravel)

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

### Запуск frontend (Next.js)

```bash
cd next
npm install
npm run dev
```

Подробности по Docker‑окружению см. в `docker/README.md`, по фронтенду — в `next/README.md`.


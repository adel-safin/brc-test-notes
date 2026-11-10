# Заметки

Сервис для просмотра, создания, правки и удаления заметок
API на Laravel, интерфейс на React

## Запуск

Нужны Docker и Docker Compose v2

```bash
docker compose up -d --build
```

Интерфейс: http://127.0.0.1:8080

Документация API: http://127.0.0.1:8080/docs/api

Остановка:

```bash
docker compose down
```

MySQL остаётся во внутреннем volume и с хоста не открывается
Пароль `notes` нужен только для локального запуска
Перед `docker compose up` его можно сменить через переменную `DB_PASSWORD`

При первом старте контейнер выполняет миграции
Локальный `APP_KEY` задан в `compose.yaml`
Его можно заменить переменной `APP_KEY`

## API

Ответ с заметкой и список обёрнуты в `data`
Поля `title` и `body` обязательные
Заголовок до 255 символов, текст до 10000
Пустые или слишком длинные значения дают 422
Чужой или нечисловой id даёт 404

## Тесты

```bash
docker compose exec app php artisan test
```

PHPUnit использует sqlite в памяти и не пишет в MySQL

## Состав

- `backend` - Laravel, PHPUnit, OpenAPI через Scramble
- `frontend` - Vite и React
- `compose.yaml` - `db`, `app`, `web`

# Laravel — "Балансы пользователей"

## Описание проекта
Система учёта балансов пользователей на Laravel 11 + Vue 3. SPA с авторизацией, дашбордом и историей операций.

## Стек технологий

| Слой | Технология |
|------|-----------|
| Backend | PHP 8.3, Laravel ^11 |
| Database | PostgreSQL (предпочтительно) / MySQL |
| Frontend | Vue 3 (Composition API), TypeScript, Vite |
| UI | Bootstrap 5.1, SCSS |
| HTTP-клиент | Axios |
| Очереди | Laravel Queues (database driver) |

## Архитектура: Domain-Driven Design

4 слоя, строгое направление зависимостей: `Presentation → Application → Domain ← Infrastructure`

### Backend (`backend/app/`)

```
backend/app/
├── Domain/                          # Чистый PHP, НИКАКИХ Illuminate-зависимостей
│   ├── Identity/
│   │   ├── Entities/User.php
│   │   ├── Repositories/UserRepositoryInterface.php
│   │   ├── Services/UserDomainService.php
│   │   ├── Exceptions/UserAlreadyExistsException.php
│   │   └── Exceptions/UserNotFoundException.php
│   └── Balance/
│       ├── Aggregates/UserBalance.php        # Aggregate Root
│       ├── Entities/Operation.php
│       ├── ValueObjects/Money.php            # Иммутабельный VO
│       ├── Repositories/BalanceRepositoryInterface.php
│       ├── Repositories/OperationRepositoryInterface.php
│       ├── Services/BalanceDomainService.php
│       ├── Events/OperationProcessed.php
│       └── Exceptions/InsufficientFundsException.php
│
├── Application/                     # Оркестрация (UseCase = Handler)
│   ├── Identity/
│   │   ├── Commands/CreateUserCommand.php    # DTO
│   │   └── Handlers/CreateUserHandler.php
│   └── Balance/
│       ├── Commands/ProcessBalanceCommand.php
│       ├── Commands/QueueBalanceOperationCommand.php
│       ├── Handlers/ProcessBalanceHandler.php
│       ├── Handlers/QueueBalanceOperationHandler.php
│       └── Queries/
│           ├── DashboardQueryHandler.php
│           └── OperationsQueryHandler.php
│
├── Infrastructure/                  # Реализации интерфейсов
│   ├── Persistence/
│   │   ├── Eloquent/Models/         # UserModel, UserBalanceModel, OperationModel
│   │   ├── Repositories/            # EloquentUserRepository, EloquentBalanceRepository, EloquentOperationRepository
│   │   └── Providers/RepositoryServiceProvider.php
│   └── Queue/Jobs/ProcessBalanceOperationJob.php
│
└── Presentation/                    # Тонкий слой ввода-вывода
    ├── Http/Controllers/            # AuthController, DashboardController, OperationsController
    ├── Http/Requests/LoginRequest.php
    └── Console/Commands/            # CreateUserCommand, BalanceOperateCommand
```

### Frontend (`backend/resources/js/`)

Vue 3 SPA — Composition API + TypeScript, маршрутизация через Vue Router.

```
resources/js/
├── app.ts                   # Точка входа, монтирование Vue
├── App.vue                  # Корневой компонент (RouterView)
├── types.ts                 # Общие TypeScript-интерфейсы
│
├── api/                     # HTTP-клиент (Axios)
│   ├── axios.ts             # Настройка экземпляра Axios (baseURL, CSRF)
│   ├── auth.ts              # login(), logout(), getUser()
│   └── balance.ts           # getDashboard(), getOperations()
│
├── router/
│   └── index.ts             # Vue Router: маршруты + guard аутентификации
│
├── pages/                   # Страницы (route-level компоненты)
│   ├── LoginPage.vue        # Форма входа
│   ├── DashboardPage.vue    # Дашборд: баланс + последние операции
│   └── OperationsPage.vue   # История операций: пагинация, поиск, сортировка
│
└── components/              # Переиспользуемые компоненты
    ├── NavBar.vue           # Верхняя навигация с кнопкой выхода
    ├── BalanceCard.vue      # Карточка текущего баланса
    ├── RecentOperations.vue # Таблица последних операций на дашборде
    ├── OperationsTable.vue  # Таблица операций с типом и статусом
    ├── Pagination.vue       # Пагинация (prev/next + номера страниц)
    ├── SearchInput.vue      # Поле поиска по описанию
    └── SortControl.vue      # Переключатель сортировки (новые/старые)
```

**Маршруты:**

| Путь | Страница | Доступ |
|------|----------|--------|
| `/login` | `LoginPage` | гость |
| `/` | `DashboardPage` | авторизован |
| `/operations` | `OperationsPage` | авторизован |

### Прочее

```
backend/
├── database/migrations/
├── resources/
│   ├── views/app.blade.php   # SPA-shell (подключает Vite)
│   └── scss/                 # Глобальные стили (Bootstrap + кастомизация)
├── routes/web.php, api.php
├── tests/                    # PHPUnit feature-тесты (48 тестов)
└── specs/                    # Спецификации доменов и API
docker/nginx/default.conf
```

## Docker

Проект запускается полностью в Docker. Все сервисы описаны в `docker-compose.yml` в корне репозитория.

### Сервисы

| Сервис | Образ | Порт |
|--------|-------|------|
| `app` | PHP 8.3-FPM (custom Dockerfile) | — |
| `nginx` | nginx:alpine | **8080:80** |
| `db` | postgres:16-alpine | 5432:5432 |
| `queue` | PHP 8.3-FPM (тот же Dockerfile) | — |
| `node` | node:20-alpine | только сборка |

**Топология:**
```
nginx → app (php-fpm:9000)
app   → db  (postgres:5432)
queue → db  (postgres:5432)
```

- `app` и `queue` — один образ (`backend/Dockerfile`), разные команды
- `node` — одноразовый контейнер для `npm run build`, в prod не работает
- `.env` — `backend/.env` (не коммитится, копируется из `.env.example`)

### Расположение Docker-файлов

```
docker-compose.yml          # корень — оркестрация
backend/Dockerfile          # PHP 8.3-FPM образ
docker/nginx/default.conf   # nginx конфиг
```

## Команды (через Makefile)

### Жизненный цикл

```bash
make install       # первый запуск: build + up + composer + migrate + key + npm build
make up            # запустить контейнеры
make down          # остановить контейнеры
make restart       # перезапустить (down + up)
make build         # пересобрать Docker-образы (--no-cache)
make ps            # статус контейнеров
make logs          # логи всех сервисов (follow)
make shell         # bash внутри контейнера app
make clean         # полная очистка: down --volumes --remove-orphans
```

### База данных

```bash
make migrate       # php artisan migrate
make seed          # php artisan db:seed
```

### Тестирование

```bash
make test          # php artisan test (PHPUnit)
```

### Качество кода

```bash
make lint          # все линтеры: pint + phpstan + deptrac + eslint
make analyse       # статический анализ backend: pint + phpstan + deptrac

make pint          # Laravel Pint — проверка code style
make pint-fix      # Laravel Pint — автоисправление

make phpstan       # PHPStan level 9 — статический анализ
make rector        # Rector — dry-run (предлагаемые изменения)
make rector-fix    # Rector — применить рефакторинг

make deptrac       # Deptrac — контроль зависимостей DDD-слоёв
make eslint        # ESLint — анализ TypeScript/Vue
```

### Фронтенд

```bash
make npm-build     # npm install && npm run build (production)
make npm-dev       # npm install && npm run dev (Vite dev-сервер)
```

### Очереди

```bash
make queue         # запустить queue worker вручную (queue=balance, tries=3)
```

### Artisan-команды проекта

```bash
make shell
php artisan user:create {name} {login} {password}
php artisan balance:operate {login} {type: credit|debit} {amount} {description} [--queue]
```

**Приложение доступно на:** `http://localhost:8080`

## Качество кода

### Backend

| Инструмент | Назначение | Конфиг |
|-----------|-----------|--------|
| **PHPStan** уровень 9 | Статический анализ | `phpstan.neon` |
| **Laravel Pint** | Code style (PSR-12 + Laravel preset) | `pint.json` |
| **Rector** | Автоматический рефакторинг (PHP 8.3, строгие типы) | `rector.php` |
| **Deptrac** | Контроль зависимостей между DDD-слоями | `deptrac.yaml` |
| **ParaTest** | Параллельный запуск PHPUnit | — |

**Порядок проверок (CI и локально):**
```
pint → phpstan → rector --dry-run → deptrac → phpunit
```

**Deptrac-слои** (запрещены нарушения направления `Domain ← Infrastructure ← Application ← Presentation`):
- `Domain` не импортирует из других слоёв
- `Application` импортирует только `Domain`
- `Infrastructure` импортирует `Domain` и `Application`
- `Presentation` импортирует `Application` (но не `Domain` напрямую)

### Frontend

| Инструмент | Назначение | Конфиг |
|-----------|-----------|--------|
| **ESLint** + `vue` + `typescript` плагины | Статический анализ + стиль | `eslint.config.js` |
| **TypeScript** | Строгая типизация | `tsconfig.json` |
| **Vite** | Сборка и dev-сервер | `vite.config.ts` |

## CI/CD

GitHub Actions (`.github/workflows/ci.yml`) — полный pipeline без деплоя:

```
[push/PR] → backend-lint → backend-static → backend-test
                         → frontend-lint → frontend-build
```

Джобы:
1. **backend-lint** — Pint (check mode)
2. **backend-static** — PHPStan level 9 + Deptrac
3. **backend-test** — PHPUnit (параллельно, PostgreSQL service)
4. **frontend-lint** — ESLint
5. **frontend-build** — `npm run build`

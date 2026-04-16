COMPOSE = docker compose
APP     = $(COMPOSE) exec app
NODE    = $(COMPOSE) run --rm node

.PHONY: help install env dirs key up down restart build migrate seed test \
        lint pint phpstan rector deptrac analyse \
        queue logs shell npm-dev npm-build eslint ps clean \
        composer-install artisan-storage

help: ## Показать список команд
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'

# ─── Жизненный цикл ──────────────────────────────────────────────────────────

install: env dirs build up composer-install migrate key artisan-storage npm-build ## Первый запуск: сборка + старт + миграции + фронтенд
	@echo "\n✓ Готово. Открыть: http://localhost:8080"

dirs: ## Создать директории storage/* и bootstrap/cache на хосте
	mkdir -p backend/storage/app/public \
	         backend/storage/framework/cache \
	         backend/storage/framework/sessions \
	         backend/storage/framework/testing \
	         backend/storage/framework/views \
	         backend/storage/logs \
	         backend/bootstrap/cache

composer-install: ## composer install + dump-autoload внутри контейнера
	$(APP) composer install --no-scripts --prefer-dist
	$(APP) composer dump-autoload --optimize
	$(APP) chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
	$(APP) chmod -R 775 /var/www/storage /var/www/bootstrap/cache

artisan-storage: ## php artisan storage:link
	$(APP) php artisan storage:link --force

env: ## Создать .env файлы из .env.example (если .env нет)
	@test -f .env              || (cp .env.example .env && sed -i "s/^UID=.*/UID=$$(id -u)/" .env && sed -i "s/^GID=.*/GID=$$(id -g)/" .env)
	@test -f backend/.env  || cp backend/.env.example backend/.env

key: ## Сгенерировать APP_KEY
	$(APP) php artisan key:generate --ansi

up: ## Запустить все контейнеры
	$(COMPOSE) up -d

down: ## Остановить все контейнеры
	$(COMPOSE) down

restart: down up ## Перезапустить контейнеры

build: ## Пересобрать Docker-образы
	$(COMPOSE) build --no-cache

# ─── База данных ─────────────────────────────────────────────────────────────

migrate: ## Запустить миграции
	$(APP) php artisan migrate --force

seed: ## Запустить сидеры
	$(APP) php artisan db:seed --force

# ─── Тестирование ────────────────────────────────────────────────────────────

test: ## Запустить PHPUnit тесты
	$(APP) php artisan test

# ─── Качество кода ───────────────────────────────────────────────────────────

lint: pint phpstan deptrac eslint ## Запустить все линтеры и анализаторы

pint: ## Laravel Pint — проверка code style
	$(APP) ./vendor/bin/pint --test

pint-fix: ## Laravel Pint — автоисправление code style
	$(APP) ./vendor/bin/pint

phpstan: ## PHPStan level 9 — статический анализ
	$(APP) ./vendor/bin/phpstan analyse --memory-limit=512M

rector: ## Rector — dry-run (показать предлагаемые изменения)
	$(APP) ./vendor/bin/rector process --dry-run

rector-fix: ## Rector — применить рефакторинг
	$(APP) ./vendor/bin/rector process

deptrac: ## Deptrac — проверка зависимостей между DDD-слоями
	$(APP) ./vendor/bin/deptrac analyse --config-file=deptrac.yaml

analyse: pint phpstan deptrac ## Полный статический анализ (без rector)

eslint: ## ESLint — анализ TypeScript/Vue
	$(NODE) npx eslint resources/js --ext .ts,.vue

# ─── Очереди ─────────────────────────────────────────────────────────────────

queue: ## Запустить queue worker вручную (разово)
	$(APP) php artisan queue:work --queue=balance --tries=3

# ─── Фронтенд ────────────────────────────────────────────────────────────────

npm-build: ## Собрать фронтенд (production)
	$(NODE) sh -c "npm install && npm run build"

npm-dev: ## Запустить Vite dev-сервер (порт 5173)
	$(COMPOSE) run --rm -p 5173:5173 node sh -c "npm install && npm run dev"

# ─── Утилиты ─────────────────────────────────────────────────────────────────

logs: ## Логи всех сервисов (follow)
	$(COMPOSE) logs -f

shell: ## Открыть bash в контейнере app
	$(APP) bash

ps: ## Показать статус контейнеров
	$(COMPOSE) ps

clean: ## Полная очистка: down + volumes + orphans
	$(COMPOSE) down --volumes --remove-orphans

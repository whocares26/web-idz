# Clothes Shop — Symfony 7

Интернет-магазин на Symfony 7 с разделением на слои
(Entity / Repository / Form+DTO / Service / Controller),
аутентификацией через Symfony Security, Twig-шаблонами,
CSRF-защитой, Doctrine-миграциями и покрытием тестами PHPUnit.

## Стек

- PHP 8.2 + Symfony 7
- Doctrine ORM 2 + Doctrine Migrations Bundle
- Symfony Security Bundle (form_login + UserInterface на сущности `User`)
- Symfony Form + Validator (валидация на уровне DTO и Entity)
- Twig
- PhpSpreadsheet / FPDF — генерация отчётов через единый интерфейс
  `App\Service\Report\ReportGeneratorInterface` (паттерн strategy + DI tag)
- PHPUnit 10 (юнит + функциональные тесты с SQLite-in-memory)
- PHPStan уровень 8 (через `phpstan-symfony`, `phpstan-doctrine`)

## Структура

```
src/
  Controller/        Тонкие контроллеры с #[Route], #[IsGranted]
  Entity/            Doctrine entities (+ Validator constraints, UniqueEntity)
  Repository/        ServiceEntityRepository + бизнес-запросы
  Form/              FormType — мапятся на DTO
  Dto/               Чистые объекты ввода (Product, OrderInput, …)
  Service/           Бизнес-логика, не зависит от HTTP
    Report/          Стратегии генерации отчётов (CSV, XLSX, PDF)
  DataFixtures/      Doctrine-фикстуры
templates/           Twig-шаблоны
migrations/          Doctrine миграции
tests/
  Unit/              Чистые юнит-тесты (без DI-контейнера)
  Functional/        WebTestCase + изолированный SQLite
config/              Symfony-конфиги (security, doctrine, …)
```

## Архитектурные решения

- **DTO + Form Type вместо `$_POST`.** `OrderInput`/`OrderItemInput` валидируются
  через атрибуты `#[Assert\…]`. Сущности не знают про HTTP.
- **`UserInterface` на User.** Symfony сам подбирает пользователя через
  `UserRepository`, проверяет пароль, выставляет роли.
- **Tagged services.** Все генераторы отчётов автоматически тегаются
  как `app.report_generator` и собираются в `ReportService` через
  `!tagged_iterator`. Чтобы добавить новый формат, достаточно создать
  класс, реализующий `ReportGeneratorInterface`.
- **Никаких `global $entityManager`.** Везде constructor injection.
- **Никаких сырых SQL-запросов в контроллерах.** Запросы — в репозиториях
  через QueryBuilder.
- **CSRF-защита** на login/register/order форме (Symfony делает это сам).
- **Authorization через `#[IsGranted('ROLE_USER')]`** + `access_control`.
- **Тесты на SQLite-in-memory** — фактически интеграционные, без mock-ов БД,
  с полной схемой, что ловит реальные ошибки маппинга.

## Требования

- Docker Desktop (Windows/macOS) **или** PHP 8.2+ и MySQL 8 локально
- ~1 GB свободного места под `vendor/` и образ MySQL
- Порты `8080` (приложение) и `3306` (MySQL) свободны

## Запуск приложения

### Вариант A — Docker (рекомендуется)

```powershell
# 1. Сборка и старт контейнеров (в фоне)
docker compose up -d --build

# 2. Установить PHP-зависимости
docker compose exec app composer install

# 3. Применить миграции БД
docker compose exec app php bin/console doctrine:migrations:migrate -n

# 4. (Опционально) Загрузить демо-данные
#    admin / admin123 (роль ROLE_ADMIN)
#    alice / alice123 (обычный пользователь, с одним заказом)
docker compose exec app php bin/console doctrine:fixtures:load -n
```

Приложение: <http://localhost:8080>

Полезные команды:

```powershell
docker compose logs -f app           # смотреть логи приложения
docker compose exec app bash         # зайти в контейнер
docker compose down                  # остановить (данные MySQL остаются в volume)
docker compose down -v               # остановить и удалить данные БД
```

### Вариант B — без Docker

```powershell
# 1. Подготовить локальный MySQL и создать БД clothes_shop
# 2. Скопировать .env → .env.local и прописать DATABASE_URL под локальную БД
copy .env .env.local

# 3. Зависимости
composer install

# 4. Миграции
php bin/console doctrine:migrations:migrate -n

# 5. (Опционально) Демо-данные
php bin/console doctrine:fixtures:load -n

# 6. Встроенный сервер Symfony / PHP
php -S localhost:8080 -t public
```

## Запуск тестов

Тесты полностью изолированы от MySQL — используют SQLite-in-memory
(см. `.env.test`), схема пересоздаётся перед каждым тестом через
`SchemaTool` в `tests/Functional/AbstractWebTestCase.php`. То есть для
тестов **не нужен запущенный контейнер `db` и миграции**.

### Все тесты сразу

```powershell
docker compose exec app vendor/bin/phpunit
# или без Docker:
vendor\bin\phpunit
```

### Только юнит-тесты

Чистая логика, без контейнера и БД — самый быстрый прогон.

```powershell
docker compose exec app vendor/bin/phpunit --testsuite unit
# или:
vendor\bin\phpunit --testsuite unit
```

### Только функциональные тесты

Поднимают Kernel и SQLite-in-memory.

```powershell
docker compose exec app vendor/bin/phpunit --testsuite functional
# или:
vendor\bin\phpunit --testsuite functional
```

### Конкретный тест / тест-файл

```powershell
# Один файл
vendor\bin\phpunit tests\Unit\Service\OrderManagerTest.php

# Один тест по имени
vendor\bin\phpunit --filter testCreateFromInputBuildsOrderAndPersistsIt
```

### Coverage-отчёт

Нужен Xdebug или pcov:

```powershell
vendor\bin\phpunit --coverage-html var\coverage
# затем открыть var/coverage/index.html
```

## Статический анализ

```powershell
docker compose exec app vendor/bin/phpstan analyse
# или:
vendor\bin\phpstan analyse
```

## Что покрыто тестами

Юнит:
- `Entity\User` — роли, идентификатор, флаг администратора.
- `Entity\Order` — двунаправленная связь с `OrderItem`, идемпотентность `addItem`.
- `Service\ProductCatalog` — фильтрация по категории, кэширование.
- `Service\OrderManager` — построение заказа из DTO, разделение видимости
  для admin/user.
- `Service\Report\OrderRowFormatter` — расплющивание заказов в строки.
- `Service\Report\CsvReportGenerator` — UTF-8 BOM, корректные заголовки.
- `Service\Report\ReportService` — диспатч по формату, 404 на неизвестном.
- `Form\OrderFormType` — маппинг данных формы в DTO.

Функциональные:
- `SecurityTest` — анонимный редирект, успех/неуспех login, logout.
- `RegistrationTest` — регистрация, валидация совпадения паролей,
  уникальность username.
- `OrderControllerTest` — рендер каталога, создание заказа, валидация,
  изоляция заказов между пользователями, admin видит всё.
- `ReportControllerTest` — корректные content-type для CSV/XLSX/PDF,
  404 на неизвестном формате, защита авторизацией.
- `CabinetTest` — личный кабинет, бейдж администратора.

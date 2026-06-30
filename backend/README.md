# Kitchen Store Backend

Production-oriented kitchenware e-commerce REST API built with Laravel, MySQL, Sanctum, queues, policies, API Resources, and service classes.

## Requirements

- PHP 8.3+
- Composer
- MySQL 8+
- PHP extensions required by Laravel, GD, and BCMath

## Local setup

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Run queued notifications in another terminal:

```bash
php artisan queue:work --queue=notifications,default --tries=3
```

Run quality checks:

```bash
php artisan test
vendor/bin/pint --test
```

## Documentation

- [REST API guide](docs/API.md)
- [Laravel routes](routes/api.php)
- [Environment template](.env.example)

The API base path is `/api/v1`. Authentication uses Laravel Sanctum bearer tokens.

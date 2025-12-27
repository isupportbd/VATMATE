<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com)**
-   **[Tighten Co.](https://tighten.co)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Redberry](https://redberry.international/laravel-development)**
-   **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

**.htaccess need for root file**

```
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ /public/$1 [L,QSA]
</IfModule>
```

**laravel 11 inertia vu3 deployment**
**unwanted cache clear command before deploy**

```
composer dump-autoload
composer install
php artisan cache:clear
php artisan clear-compiled
```

**faster route command**

```
php artisan route:cache
```

**remove route cache command**

```
php artisan route:clear
```

**Laravel 12 module delete**

```
php artisan module:disable module_name
php artisan cache:clear
php artisan module:delete module_name
php artisan cache:clear
```

**Laravel 12 Event/Listener create, delete, update**

```
php artisan make:event event_name (for create)
php artisan make:listener event_name (for create)

composer dump-autoload / php artisan clear-compiled (for delete)
php artisan event:generate (for delete)
```

**Laravel 12 Cpanel CRON run**

```
/usr/local/bin/php /home/qoreldesk/authstation.com/artisan schedule:run >> /dev/null 2>&1
```

**💥 Build & Run (Local or VPS)**

-   Install PHP and JS dependencies, build assets, and cache config before deploy

```
docker compose up --build -d

docker exec -it laravel-app bash

composer install --no-dev --optimize-autoloader
npm install && npm run build

php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**hostinger vps**

-   new upload and give permission for database

```
docker exec -it mysql mysql -u root -p

GRANT ALL PRIVILEGES ON stats.* TO 'niyam'@'%';
FLUSH PRIVILEGES;
```

**after deployment for local environment**

```
composer install --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-posix
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:clear
php artisan view:clear
```

**existing database upload from local machine to vps docker(run in powershell)**

```
ONE-SHOT IMPORT (PowerShell → VPS → Docker MySQL)

Paste this into PowerShell on your Windows PC:

$backupPath = "$env:USERPROFILE\Desktop\backup sarra\sarra.sql"

Get-Content -Raw $backupPath |
ssh niyam@YOUR_VPS_IP "docker exec -i mysql-sarra mysql -u root -p'YOUR_ROOT_PASSWORD' sarra"


Replace:

YOUR_VPS_IP → VPS IP

YOUR_ROOT_PASSWORD → MYSQL_ROOT_PASSWORD you set in compose

Example:

$backupPath = "$env:USERPROFILE\Desktop\backup sarra\sarra.sql"

Get-Content -Raw $backupPath |
ssh niyam@198.51.100.25 "docker exec -i mysql-global mysql -u root -p'MyRoot123!' sarra"
```

**Alternative existing database upload from local machine to vps docker(run in powershell)**

```
ALTERNATIVE (safer, recommended for large SQL files)
Step A — Copy SQL file to VPS

Run this in PowerShell:

scp "$env:USERPROFILE\Desktop\backup sarra\sarra.sql" niyam@YOUR_VPS_IP:/home/niyam/sarra.sql


Example:

scp "$env:USERPROFILE\Desktop\backup sarra\sarra.sql" niyam@198.51.100.25:/home/niyam/sarra.sql

Step B — SSH into VPS and import
ssh niyam@YOUR_VPS_IP


Then run:

docker exec -i mysql-sarra mysql -u root -p'YOUR_ROOT_PASSWORD' sarra < /home/niyam/sarra.sql


Example:

docker exec -i mysql-sarra mysql -u root -p'MyRoot123!' sarra < /home/niyam/sarra.sql

Step C — Remove SQL file (optional)
rm /home/niyam/sarra.sql
```

**After mysql to postgre sql sequence rest**

-   use dbeaver for mysql to postgre database data migrate
-   run this directly on psql window

```
DO $$
DECLARE
    r RECORD;
BEGIN
    FOR r IN
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'public'
          AND table_type = 'BASE TABLE'
    LOOP
        BEGIN
            EXECUTE format('SELECT setval(pg_get_serial_sequence(%L, ''id''), COALESCE((SELECT MAX(id) FROM %I), 1))', r.table_name, r.table_name);
        EXCEPTION WHEN OTHERS THEN
            -- Skip tables without id column
        END;
    END LOOP;
END $$;
```

**From local to vps postgre database upload existing data**

-   must export data as a .dump
-   dump as sql (local windows powershell)

```
& "F:\LARAGON\bin\postgresql\postgresql\bin\pg_dump.exe" `
  -h localhost -U postgres -d vatmate `
  --format=p `
  --no-owner `
  --no-privileges `
  --no-comments `
  > "C:\Users\niyam\OneDrive\Desktop\vatmate.sql"
```

-   if database as a .sql (recomanded) and it is workable for plain sql

```
Get-Content -Raw "C:\Users\niyam\OneDrive\Desktop\dump-vatmate-202512280148.sql" |
ForEach-Object { $_ -replace '(?m)^\s*SET\s+(transaction_timeout|idle_session_timeout)\s*=\s*[^;]+;\s*[\r\n]*','' } |
ssh niyam@89.116.122.250 "docker exec -i postgres-global psql -U postgres -d vatmate -v ON_ERROR_STOP=1"
```

-   if database as a .sql with schema + data

```
Get-Content -Raw "C:\Users\niyam\OneDrive\Desktop\dump-vatmate-202512280114.sql" |
ForEach-Object { $_ -replace '(?m)^\s*SET\s+(transaction_timeout|idle_session_timeout|statement_timeout)\s*=\s*[^;]+;\s*[\r\n]*','' } |
ssh niyam@89.116.122.250 "docker exec -i postgres-global psql -U postgres -d vatmate -v ON_ERROR_STOP=1 -c 'DROP SCHEMA public CASCADE; CREATE SCHEMA public;' && docker exec -i postgres-global psql -U postgres -d vatmate -v ON_ERROR_STOP=1"

ssh niyam@89.116.122.250 "docker exec -i postgres-global psql -U postgres -d vatmate -v ON_ERROR_STOP=1 -c 'DROP SCHEMA public CASCADE; CREATE SCHEMA public;'" ; `
Get-Content -Encoding Byte "C:\Users\niyam\OneDrive\Desktop\dump-vatmate-202512280114.sql" |
ssh niyam@89.116.122.250 "docker exec -i postgres-global pg_restore -U postgres -d vatmate --no-owner --no-privileges --verbose"

```

-   If you want DROP+RECREATE style (clean import). This removes existing objects first (be careful on production).

```
Get-Content -Raw "$env:USERPROFILE\Desktop\sarra.sql" |
ssh niyam@198.51.100.25 "docker exec -i postgres-global psql -U postgres -d sarra -v ON_ERROR_STOP=1"
```

-   If you want it to drop and recreate the public schema before import (strong clean):

```
ssh niyam@198.51.100.25 "docker exec -i postgres-global psql -U postgres -d sarra -c 'DROP SCHEMA public CASCADE; CREATE SCHEMA public;'"
Get-Content -Raw "$env:USERPROFILE\Desktop\sarra.sql" |
ssh niyam@198.51.100.25 "docker exec -i postgres-global psql -U postgres -d sarra -v ON_ERROR_STOP=1"
```

##  Laravel 10 Bilerplat

Yang dibutuhkan :
 - composer
 - php versi 8.1 keatas

## Install Dependency
```
composer install
```

## Copy .env & Generate APP KEY
copy file .env.example ke .env
```
cp .env.example .env
```
Sesuaikan .env dengan konfigurasi database, storage, session dll

Generate app key dengan
```
php artisan key:generate
```

## Database Migration
Jalankan database migration
```
php artisan migrate
```

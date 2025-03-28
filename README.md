## Paso 1 - Cambiar el .env.example a .env

Configurar eso:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bolsa-empleo
DB_USERNAME=root
DB_PASSWORD=
```

## Paso 2 - Instalar paquetes

```
composer install
```

(tarda en generar los archivos autoload)

## Paso 3 - Ejecutar Migraciones

```
php artisan migrate
```

## Paso 3 - Arrancar Laravel

```
php artisan serve
```

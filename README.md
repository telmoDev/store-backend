# Store Backend API (Procesos Almacenados)

Este es un proyecto Laravel diseñado para gestionar una tienda, utilizando **Procedimientos Almacenados (Stored Procedures)** en MySQL para toda la lógica de negocio y persistencia de datos.

## Requisitos

-   Docker y Docker Compose
-   [Laravel Sail](https://laravel.com/docs/sail) (incluido)

## Instalación

Sigue estos pasos para configurar el proyecto localmente:

1.  **Clonar el repositorio:**
    ```bash
    git clone <url-del-repositorio>
    cd store
    ```

2.  **Configurar el entorno:**
    ```bash
    cp .env.example .env
    ```
    *(Asegúrate de que `DB_HOST=mysql` y las credenciales coincidan con las de Sail)*

3.  **Instalar dependencias:**
    ```bash
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd):/var/www/html" \
        -w /var/www/html \
        laravelsail/php83-composer:latest \
        composer install --ignore-platform-reqs
    ```

4.  **Levantar servidores (Sail):**
    ```bash
    ./vendor/bin/sail up -d
    ```

5.  **Generar App Key:**
    ```bash
    ./vendor/bin/sail artisan key:generate
    ```

6.  **Ejecutar Migraciones:**
    Este paso creará las tablas y todos los **Procedimientos Almacenados** individuales.
    ```bash
    ./vendor/bin/sail artisan migrate:fresh
    ```

## Documentación de la API

### Facturación / Pedidos
-   `GET /api/pedidos`: Lista todos los pedidos.
    -   **Filtros opcionales:**
        -   `desde`: Fecha inicio (YYYY-MM-DD)
        -   `hasta`: Fecha fin (YYYY-MM-DD)
        -   `minTotal`: Monto mínimo de la orden.
-   `POST /api/pedidos`: Crea un nuevo pedido usando el SP transaccional `sp_create_order`.
    -   Calcula automáticamente IVA (12%) y Descuento (10% si total > $100).

### Productos
-   `GET /api/productos`: Lista paginada de productos con búsqueda por nombre/SKU.

## Pruebas

El proyecto cuenta con pruebas automatizadas para validar la lógica de los servicios y los SPs.

```bash
./vendor/bin/sail artisan test
```

## Arquitectura

-   **Migrations**: Cada procedimiento almacenado tiene su propio archivo de migración en `database/migrations/` para facilitar su mantenimiento.
-   **Services**: Los servicios actúan como puentes, llamando a los SPs mediante `DB::select` o `DB::statement`.
-   **Controllers**: Manejan la entrada del usuario y devuelven respuestas mediante `JsonResources`.

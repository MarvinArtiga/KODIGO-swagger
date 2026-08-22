# Ecommerce API - Guía de prueba

Esta guía te permite levantar la API y probar todos los endpoints de forma rápida.

## Requisitos

- PHP 8.3+
- Composer
- MySQL
- Laravel 12

## 1. Instalar dependencias

```bash
composer install
```

## 2. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Revisa tu archivo `.env` y asegúrate de tener valores parecidos a estos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=tu_clave_generada
JWT_TTL=60

STRIPE_KEY=
STRIPE_SECRET=
```

> Si no tienes MySQL configurado, créalo primero y asegúrate de que la base `ecommerce` exista.

## 3. Crear la base de datos y correr migraciones

```bash
php artisan migrate
```

## 4. Ejecutar seeders

```bash
php artisan db:seed
```

O para reinicializar todo:

```bash
php artisan migrate:fresh --seed
```

## 5. Levantar el servidor

```bash
php artisan serve
```

La API quedará disponible en:

```text
http://localhost:8000
```

Swagger:

```text
http://localhost:8000/api/documentation
```

## 6. Probar la API

### Registro

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Juan Perez",
    "email": "juan@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "juan@example.com",
    "password": "password123"
  }'
```

Copia el valor de `data.token` recibido en la respuesta.

### Perfil autenticado

```bash
TOKEN="tu_token_aqui"

curl http://localhost:8000/api/me \
  -H "Authorization: Bearer $TOKEN"
```

### Logout

```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer $TOKEN"
```

### Listar productos públicos

```bash
curl http://localhost:8000/api/products
```

### Ver un producto

```bash
curl http://localhost:8000/api/products/1
```

### Crear producto (requiere JWT)

```bash
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Laptop Gamer",
    "description": "Laptop para gaming",
    "price": 1200.00,
    "stock": 10
  }'
```

### Actualizar producto

```bash
curl -X PUT http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Laptop Gamer Pro",
    "price": 1350.00,
    "stock": 7
  }'
```

### Eliminar producto

```bash
curl -X DELETE http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer $TOKEN"
```

### Crear una orden

```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      { "product_id": 1, "quantity": 2 }
    ]
  }'
```

### Listar órdenes del usuario autenticado

```bash
curl http://localhost:8000/api/orders \
  -H "Authorization: Bearer $TOKEN"
```

### Ver una orden específica

```bash
curl http://localhost:8000/api/orders/1 \
  -H "Authorization: Bearer $TOKEN"
```

### Crear PaymentIntent con Stripe

```bash
curl -X POST http://localhost:8000/api/payments/1 \
  -H "Authorization: Bearer $TOKEN"
```

> Esto requiere que `STRIPE_SECRET` esté configurado correctamente.

## 7. Probar desde Swagger

Abre:

```text
http://localhost:8000/api/documentation
```

Y en la parte superior haz clic en "Authorize", luego escribe:

```text
Bearer tu_token_aqui
```

Así podrás probar cada endpoint directamente desde la UI de Swagger.

## 8. Verificar rutas disponibles

```bash
php artisan route:list
```

Busca estas rutas:

- `POST /api/register`
- `POST /api/login`
- `POST /api/logout`
- `GET /api/me`
- `GET /api/products`
- `GET /api/products/{id}`
- `POST /api/products`
- `PUT /api/products/{id}`
- `DELETE /api/products/{id}`
- `POST /api/orders`
- `GET /api/orders`
- `GET /api/orders/{id}`
- `POST /api/payments/{order}`

## 9. Variables importantes

```env
APP_KEY=
JWT_SECRET=
STRIPE_KEY=
STRIPE_SECRET=
```

## 10. Solución rápida si falla

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan migrate:fresh --seed
php artisan serve
```

Si quieres, también te puedo dejar una versión de este README en formato más corto para copiar/pegar en GitHub o en un archivo de documentación de equipo.

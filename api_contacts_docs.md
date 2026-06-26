# 📋 Documentación de la API REST — Red App

**Base URL:** `http://localhost:8080/api`  
**Content-Type:** `application/json`  
**Base de datos:** PostgreSQL (Neon Serverless)

---

## 📌 Tabla de Contenidos

1. [Contactos — Endpoints](#-contactos--endpoints)
   - [GET /contacts](#1-get-apicontacts--listar-todos-los-contactos)
   - [POST /contacts](#2-post-apicontacts--crear-contacto)
   - [GET /contacts/{id}](#3-get-apicontactsid--ver-un-contacto)
   - [PATCH /contacts/{id}](#4-patch-apicontactsid--actualizar-contacto)
   - [DELETE /contacts/{id}](#5-delete-apicontactsid--eliminar-contacto)
2. [Imágenes — Endpoints](#️-imágenes--endpoints)
   - [GET /contacts/{contact}/images](#1-get-apicontactscontactimages--listar-imágenes)
   - [POST /contacts/{contact}/images](#2-post-apicontactscontactimages--agregar-imagen)
   - [GET /images/{image}](#3-get-apiimagesimage--ver-una-imagen)
   - [PATCH /images/{image}](#4-patch-apiimagesimage--actualizar-imagen)
   - [DELETE /images/{image}](#5-delete-apiimagesimage--eliminar-imagen)
3. [Estructura de Respuestas](#-estructura-de-respuestas)
4. [Códigos de Error](#-códigos-de-error)
5. [Resumen de Rutas](#️-resumen-de-rutas)

---

## 👤 Contactos — Endpoints

Registrado en `routes/api.php` como:
```php
Route::apiResource('contacts', ContactController::class);
```

---

### 1. `GET /api/contacts` — Listar todos los contactos

Retorna todos los contactos con sus imágenes asociadas.

| Campo        | Valor                                    |
|-------------|------------------------------------------|
| **Método**  | `GET`                                    |
| **URL**     | `http://localhost:8080/api/contacts`     |
| **Auth**    | No requerida                             |
| **Body**    | Ninguno                                  |

#### ✅ Respuesta exitosa `200 OK`

```json
{
  "success": true,
  "count": 2,
  "data": [
    {
      "id": 1,
      "nombre": "Juan Pérez",
      "numero": "5551234567",
      "usuario": "juanperez",
      "correo": "juan@example.com",
      "images": [
        {
          "id": 1,
          "url": "https://cdn.example.com/avatars/juan.jpg",
          "alt": "Foto de perfil de Juan"
        }
      ]
    },
    {
      "id": 2,
      "nombre": "María López",
      "numero": "5559876543",
      "usuario": "marialopez",
      "correo": "maria@example.com",
      "images": []
    }
  ]
}
```

---

### 2. `POST /api/contacts` — Crear contacto

Crea un nuevo contacto. Todos los campos son **obligatorios**.

| Campo             | Valor                                    |
|------------------|------------------------------------------|
| **Método**       | `POST`                                   |
| **URL**          | `http://localhost:8080/api/contacts`     |
| **Auth**         | No requerida                             |
| **Content-Type** | `application/json`                       |

#### 📥 Body (Request)

```json
{
  "nombre":     "Carlos Ruiz",
  "numero":     "5551112233",
  "usuario":    "carlosruiz",
  "contrasena": "miPassword123",
  "correo":     "carlos@example.com"
}
```

#### 📋 Validaciones de campos

| Campo        | Reglas                                                              |
|-------------|---------------------------------------------------------------------|
| `nombre`    | Requerido · Solo letras y espacios (acentos y ñ permitidos) · Máx 50 |
| `numero`    | Requerido · Solo dígitos · Máx 15                                   |
| `usuario`   | Requerido · Alfanumérico · Único · Máx 50                           |
| `contrasena`| Requerido · Min 6 · Máx 100 · Se guarda como **hash bcrypt**        |
| `correo`    | Requerido · Formato email · Único · Máx 100                         |

> ⚠️ `contrasena` **nunca se devuelve** en las respuestas (oculto por el modelo).

#### ✅ Respuesta exitosa `201 Created`

```json
{
  "success": true,
  "message": "Contacto creado exitosamente",
  "data": {
    "id": 3,
    "nombre": "Carlos Ruiz",
    "numero": "5551112233",
    "usuario": "carlosruiz",
    "correo": "carlos@example.com",
    "images": []
  }
}
```

#### ❌ Error de validación `422 Unprocessable Entity`

```json
{
  "message": "The usuario has already been taken. (and 1 more error)",
  "errors": {
    "usuario": ["The usuario has already been taken."],
    "correo":  ["The correo has already been taken."]
  }
}
```

---

### 3. `GET /api/contacts/{id}` — Ver un contacto

| Campo          | Valor                                      |
|---------------|-------------------------------------------|
| **Método**    | `GET`                                      |
| **URL**       | `http://localhost:8080/api/contacts/1`     |
| **Parámetro** | `{id}` — ID numérico del contacto          |

#### ✅ Respuesta exitosa `200 OK`

```json
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Juan Pérez",
    "numero": "5551234567",
    "usuario": "juanperez",
    "correo": "juan@example.com",
    "images": [
      {
        "id": 1,
        "url": "https://cdn.example.com/avatars/juan.jpg",
        "alt": "Foto de perfil de Juan"
      }
    ]
  }
}
```

#### ❌ `404 Not Found`

```json
{
  "message": "No query results for model [App\\Models\\Contact] 999"
}
```

---

### 4. `PATCH /api/contacts/{id}` — Actualizar contacto

Todos los campos son **opcionales** (solo envía los que deseas cambiar).

| Campo             | Valor                                       |
|------------------|---------------------------------------------|
| **Método**       | `PATCH`                                     |
| **URL**          | `http://localhost:8080/api/contacts/1`      |
| **Content-Type** | `application/json`                          |

> 💡 `PUT` también funciona con la misma URL para actualización completa.

#### 📥 Body — Solo el número

```json
{
  "numero": "5550001111"
}
```

#### 📥 Body — Varios campos

```json
{
  "nombre":     "Juan Pedro Pérez",
  "correo":     "juanpedro@example.com",
  "contrasena": "nuevaPassword456"
}
```

#### ✅ Respuesta exitosa `200 OK`

```json
{
  "success": true,
  "message": "Contacto actualizado exitosamente",
  "data": {
    "id": 1,
    "nombre": "Juan Pedro Pérez",
    "numero": "5550001111",
    "usuario": "juanperez",
    "correo": "juanpedro@example.com",
    "images": []
  }
}
```

---

### 5. `DELETE /api/contacts/{id}` — Eliminar contacto

| Campo          | Valor                                      |
|---------------|-------------------------------------------|
| **Método**    | `DELETE`                                   |
| **URL**       | `http://localhost:8080/api/contacts/1`     |
| **Body**      | Ninguno                                    |

#### ✅ Respuesta exitosa `200 OK`

```json
{
  "success": true,
  "message": "Contacto eliminado exitosamente"
}
```

---

## 🖼️ Imágenes — Endpoints

Las imágenes usan una **relación polimórfica** (`morphMany`) con los contactos.  
Registrado en `routes/api.php` con `->shallow()` para URLs limpias:

```php
Route::apiResource('contacts.images', ImageController::class)->shallow();
```

> ℹ️ `shallow()` significa:
> - `index` y `store` → usan `/contacts/{contact}/images`
> - `show`, `update`, `destroy` → usan `/images/{image}` (sin repetir el padre)

---

### 1. `GET /api/contacts/{contact}/images` — Listar imágenes

Retorna todas las imágenes de un contacto, ordenadas de más reciente a más antigua.

| Campo          | Valor                                                     |
|---------------|----------------------------------------------------------|
| **Método**    | `GET`                                                     |
| **URL**       | `http://localhost:8080/api/contacts/1/images`             |
| **Auth**      | No requerida                                              |
| **Body**      | Ninguno                                                   |

#### ✅ Respuesta exitosa `200 OK`

```json
{
  "success": true,
  "count": 2,
  "data": [
    {
      "id": 3,
      "url": "https://cdn.example.com/photos/foto2.jpg",
      "alt": "Segunda foto"
    },
    {
      "id": 1,
      "url": "https://cdn.example.com/avatars/juan.jpg",
      "alt": "Foto de perfil"
    }
  ]
}
```

#### ❌ Contacto no existe `404 Not Found`

```json
{
  "message": "No query results for model [App\\Models\\Contact] 999"
}
```

---

### 2. `POST /api/contacts/{contact}/images` — Agregar imagen

Crea y asocia una nueva imagen al contacto especificado.

| Campo             | Valor                                                     |
|------------------|----------------------------------------------------------|
| **Método**       | `POST`                                                    |
| **URL**          | `http://localhost:8080/api/contacts/1/images`             |
| **Content-Type** | `application/json`                                        |

#### 📥 Body (Request)

```json
{
  "url": "https://cdn.example.com/avatars/nuevo.jpg",
  "alt": "Nueva foto de perfil"
}
```

#### 📋 Validaciones de campos

| Campo  | Reglas                                           |
|-------|--------------------------------------------------|
| `url` | Requerido · Formato URL válida · Máx 500 chars   |
| `alt` | Opcional · String · Máx 150 chars                |

#### ✅ Respuesta exitosa `201 Created`

```json
{
  "success": true,
  "message": "Imagen agregada exitosamente",
  "data": {
    "id": 5,
    "url": "https://cdn.example.com/avatars/nuevo.jpg",
    "alt": "Nueva foto de perfil"
  }
}
```

#### ❌ Error de validación `422 Unprocessable Entity`

```json
{
  "message": "The url field must be a valid URL.",
  "errors": {
    "url": ["The url field must be a valid URL."]
  }
}
```

---

### 3. `GET /api/images/{image}` — Ver una imagen

> ℹ️ Con `shallow()`, la URL no repite el padre (`/contacts/{id}`).

| Campo          | Valor                                          |
|---------------|------------------------------------------------|
| **Método**    | `GET`                                          |
| **URL**       | `http://localhost:8080/api/images/1`           |
| **Body**      | Ninguno                                        |

#### ✅ Respuesta exitosa `200 OK`

```json
{
  "success": true,
  "data": {
    "id": 1,
    "url": "https://cdn.example.com/avatars/juan.jpg",
    "alt": "Foto de perfil de Juan"
  }
}
```

#### ❌ Imagen no pertenece al contacto `404 Not Found`

```json
{
  "message": "Imagen no encontrada para este contacto."
}
```

---

### 4. `PATCH /api/images/{image}` — Actualizar imagen

Actualiza la URL y/o el texto alternativo de una imagen. Campos **opcionales**.

| Campo             | Valor                                          |
|------------------|------------------------------------------------|
| **Método**       | `PATCH`                                        |
| **URL**          | `http://localhost:8080/api/images/1`           |
| **Content-Type** | `application/json`                             |

#### 📥 Body — Actualizar solo el alt

```json
{
  "alt": "Foto de perfil actualizada"
}
```

#### 📥 Body — Actualizar ambos campos

```json
{
  "url": "https://cdn.example.com/avatars/nueva-foto.jpg",
  "alt": "Nueva foto de perfil"
}
```

#### ✅ Respuesta exitosa `200 OK`

```json
{
  "success": true,
  "message": "Imagen actualizada exitosamente",
  "data": {
    "id": 1,
    "url": "https://cdn.example.com/avatars/nueva-foto.jpg",
    "alt": "Nueva foto de perfil"
  }
}
```

---

### 5. `DELETE /api/images/{image}` — Eliminar imagen

Elimina permanentemente una imagen.

| Campo          | Valor                                          |
|---------------|------------------------------------------------|
| **Método**    | `DELETE`                                       |
| **URL**       | `http://localhost:8080/api/images/1`           |
| **Body**      | Ninguno                                        |

#### ✅ Respuesta exitosa `200 OK`

```json
{
  "success": true,
  "message": "Imagen eliminada exitosamente"
}
```

---

## 📐 Estructura de Respuestas

### Objeto Contacto

```json
{
  "id":      1,
  "nombre":  "Juan Pérez",
  "numero":  "5551234567",
  "usuario": "juanperez",
  "correo":  "juan@example.com",
  "images": [
    { "id": 1, "url": "https://...", "alt": "Descripción" }
  ]
}
```

> ℹ️ Campos nunca expuestos: `contrasena`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`

### Objeto Imagen

```json
{
  "id":  1,
  "url": "https://cdn.example.com/foto.jpg",
  "alt": "Descripción de la imagen"
}
```

> ℹ️ Campos nunca expuestos: `imageable_id`, `imageable_type`, `created_at`, `updated_at`

---

## 🚨 Códigos de Error

| Código | Significado               | Cuándo ocurre                                       |
|-------|---------------------------|-----------------------------------------------------|
| `200` | OK                        | Operación exitosa (GET, PATCH, DELETE)              |
| `201` | Created                   | Recurso creado exitosamente (POST)                  |
| `404` | Not Found                 | El `{id}` no existe o imagen no pertenece al contacto |
| `422` | Unprocessable Entity      | Fallo de validación                                 |
| `500` | Internal Server Error     | Error interno del servidor                          |

---

## 🗺️ Resumen de Rutas

### Contactos

| Método        | URL                      | Controlador              | Descripción                    |
|-------------|--------------------------|--------------------------|-------------------------------|
| `GET`       | `/api/contacts`          | `ContactController@index`   | Listar todos                  |
| `POST`      | `/api/contacts`          | `ContactController@store`   | Crear nuevo                   |
| `GET`       | `/api/contacts/{id}`     | `ContactController@show`    | Ver uno por ID                |
| `PUT/PATCH` | `/api/contacts/{id}`     | `ContactController@update`  | Actualizar (total o parcial)  |
| `DELETE`    | `/api/contacts/{id}`     | `ContactController@destroy` | Eliminar                      |

### Imágenes (shallow nested resource)

| Método        | URL                                      | Controlador             | Descripción                      |
|-------------|------------------------------------------|-------------------------|----------------------------------|
| `GET`       | `/api/contacts/{contact}/images`         | `ImageController@index`   | Listar imágenes del contacto    |
| `POST`      | `/api/contacts/{contact}/images`         | `ImageController@store`   | Agregar imagen al contacto      |
| `GET`       | `/api/images/{image}`                    | `ImageController@show`    | Ver imagen por ID               |
| `PUT/PATCH` | `/api/images/{image}`                    | `ImageController@update`  | Actualizar imagen               |
| `DELETE`    | `/api/images/{image}`                    | `ImageController@destroy` | Eliminar imagen                 |

---

*Actualizado el 2026-06-22 — Laravel 12 · PHP · PostgreSQL (Neon)*

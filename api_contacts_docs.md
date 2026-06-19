# 📡 API de Contactos — Documentación para App Móvil

> **Backend**: Laravel 11 — REST API  
> **Base URL**: `http://<TU_SERVIDOR>/api`  
> **Formato**: JSON  
> **Header requerido en todas las peticiones**:
> ```
> Content-Type: application/json
> Accept: application/json
> ```

---

## 📋 Resumen de Endpoints

| # | Método | Endpoint | Acción | Descripción |
|---|--------|----------|--------|-------------|
| 1 | `GET` | `/api/contacts` | `index` | Lista todos los contactos |
| 2 | `POST` | `/api/contacts` | `store` | Crea un nuevo contacto |
| 3 | `GET` | `/api/contacts/{id}` | `show` | Muestra un contacto por ID |
| 4 | `PUT` | `/api/contacts/{id}` | `update` | Actualiza un contacto completo |
| 5 | `PATCH` | `/api/contacts/{id}` | `update` | Actualiza campos parciales |
| 6 | `DELETE` | `/api/contacts/{id}` | `destroy` | Elimina un contacto |

---

## 📐 Modelo de Datos — Contact

| Campo | Tipo | Requerido | Restricciones |
|-------|------|-----------|---------------|
| `id` | integer | Auto | Generado por el servidor |
| `nombre` | string | ✅ | Máx. 50 chars. Solo letras y espacios (acepta acentos y ñ) |
| `numero` | string | ✅ | Máx. 15 chars. Solo dígitos (0-9) |
| `usuario` | string | ✅ | Máx. 50 chars. Solo alfanumérico. **Único en el sistema** |
| `contrasena` | string | ✅ | Mín. 6, Máx. 100 chars. Se guarda hasheada, **nunca se devuelve** |
| `correo` | string | ✅ | Formato email válido. Máx. 100 chars. **Único en el sistema** |
| `created_at` | timestamp | Auto | Generado por el servidor |
| `updated_at` | timestamp | Auto | Generado por el servidor |

> ⚠️ El campo `contrasena` **nunca aparece en ninguna respuesta JSON** por seguridad.

---

## 1️⃣ GET `/api/contacts` — Listar todos los contactos

### Descripción
Devuelve el listado completo de todos los contactos registrados en la base de datos.

### Request
```
GET http://<TU_SERVIDOR>/api/contacts
```

**Headers:**
```
Accept: application/json
```

**Body:** _(no aplica)_

---

### Respuesta exitosa — `200 OK`
```json
{
  "success": true,
  "count": 2,
  "data": [
    {
      "id": 1,
      "nombre": "Juan Pérez",
      "numero": "5512345678",
      "usuario": "juanperez",
      "correo": "juan@ejemplo.com",
      "created_at": "2026-06-18T20:00:00.000000Z",
      "updated_at": "2026-06-18T20:00:00.000000Z"
    },
    {
      "id": 2,
      "nombre": "María García",
      "numero": "5598765432",
      "usuario": "mariagarcia",
      "correo": "maria@ejemplo.com",
      "created_at": "2026-06-18T21:00:00.000000Z",
      "updated_at": "2026-06-18T21:00:00.000000Z"
    }
  ]
}
```

---

## 2️⃣ POST `/api/contacts` — Crear un contacto

### Descripción
Registra un nuevo contacto en el sistema. El `usuario` y `correo` deben ser únicos.

### Request
```
POST http://<TU_SERVIDOR>/api/contacts
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body JSON:**
```json
{
  "nombre": "Juan Pérez",
  "numero": "5512345678",
  "usuario": "juanperez",
  "contrasena": "segura123",
  "correo": "juan@ejemplo.com"
}
```

### Validaciones de campos

| Campo | Reglas |
|-------|--------|
| `nombre` | Requerido. Solo letras, espacios, acentos y ñ. Máx. 50 chars. |
| `numero` | Requerido. Solo dígitos. Máx. 15 chars. |
| `usuario` | Requerido. Alfanumérico (sin símbolos). Máx. 50 chars. Único. |
| `contrasena` | Requerido. Mín. 6 chars. Máx. 100 chars. |
| `correo` | Requerido. Email válido. Máx. 100 chars. Único. |

---

### Respuesta exitosa — `201 Created`
```json
{
  "success": true,
  "message": "Contacto creado exitosamente",
  "data": {
    "id": 3,
    "nombre": "Juan Pérez",
    "numero": "5512345678",
    "usuario": "juanperez",
    "correo": "juan@ejemplo.com",
    "created_at": "2026-06-18T22:00:00.000000Z",
    "updated_at": "2026-06-18T22:00:00.000000Z"
  }
}
```

### Respuesta de error — `422 Unprocessable Entity`
_(Cuando algún campo falla la validación)_
```json
{
  "message": "The correo has already been taken.",
  "errors": {
    "correo": [
      "The correo has already been taken."
    ],
    "usuario": [
      "The usuario has already been taken."
    ]
  }
}
```

---

## 3️⃣ GET `/api/contacts/{id}` — Mostrar un contacto

### Descripción
Devuelve los datos de un contacto específico por su ID.

### Request
```
GET http://<TU_SERVIDOR>/api/contacts/1
```

**Headers:**
```
Accept: application/json
```

**Body:** _(no aplica)_

---

### Respuesta exitosa — `200 OK`
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Juan Pérez",
    "numero": "5512345678",
    "usuario": "juanperez",
    "correo": "juan@ejemplo.com",
    "created_at": "2026-06-18T20:00:00.000000Z",
    "updated_at": "2026-06-18T20:00:00.000000Z"
  }
}
```

### Respuesta de error — `404 Not Found`
_(Cuando el ID no existe)_
```json
{
  "message": "No query results for model [App\\Models\\Contact] 99"
}
```

---

## 4️⃣ PUT `/api/contacts/{id}` — Actualizar un contacto (completo)

### Descripción
Actualiza todos los campos de un contacto existente. Se recomienda usar `PATCH` si solo se desean actualizar algunos campos.

### Request
```
PUT http://<TU_SERVIDOR>/api/contacts/1
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body JSON (todos los campos):**
```json
{
  "nombre": "Juan Carlos Pérez",
  "numero": "5511111111",
  "usuario": "juancarlos",
  "contrasena": "nuevaclave456",
  "correo": "juancarlos@ejemplo.com"
}
```

---

### Respuesta exitosa — `200 OK`
```json
{
  "success": true,
  "message": "Contacto actualizado exitosamente",
  "data": {
    "id": 1,
    "nombre": "Juan Carlos Pérez",
    "numero": "5511111111",
    "usuario": "juancarlos",
    "correo": "juancarlos@ejemplo.com",
    "created_at": "2026-06-18T20:00:00.000000Z",
    "updated_at": "2026-06-18T23:00:00.000000Z"
  }
}
```

---

## 5️⃣ PATCH `/api/contacts/{id}` — Actualizar campos parciales

### Descripción
Actualiza **solo los campos que se envíen**. Útil para actualizar únicamente el correo o el número sin necesidad de mandar todos los datos.

### Request
```
PATCH http://<TU_SERVIDOR>/api/contacts/1
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body JSON (solo campos a modificar):**
```json
{
  "numero": "5599999999"
}
```

---

### Respuesta exitosa — `200 OK`
```json
{
  "success": true,
  "message": "Contacto actualizado exitosamente",
  "data": {
    "id": 1,
    "nombre": "Juan Pérez",
    "numero": "5599999999",
    "usuario": "juanperez",
    "correo": "juan@ejemplo.com",
    "created_at": "2026-06-18T20:00:00.000000Z",
    "updated_at": "2026-06-18T23:30:00.000000Z"
  }
}
```

---

## 6️⃣ DELETE `/api/contacts/{id}` — Eliminar un contacto

### Descripción
Elimina permanentemente un contacto por su ID.

### Request
```
DELETE http://<TU_SERVIDOR>/api/contacts/1
```

**Headers:**
```
Accept: application/json
```

**Body:** _(no aplica)_

---

### Respuesta exitosa — `200 OK`
```json
{
  "success": true,
  "message": "Contacto eliminado exitosamente"
}
```

### Respuesta de error — `404 Not Found`
```json
{
  "message": "No query results for model [App\\Models\\Contact] 99"
}
```

---

## ⚠️ Manejo de Errores — Referencia Rápida

| Código HTTP | Significado | Cuándo ocurre |
|-------------|-------------|---------------|
| `200` | OK | GET, PUT, PATCH, DELETE exitosos |
| `201` | Created | POST exitoso (contacto creado) |
| `404` | Not Found | El `{id}` no existe en la BD |
| `422` | Unprocessable Entity | Falla de validación en los campos |
| `500` | Server Error | Error interno del servidor |

---

## 🔧 Notas de Implementación para el Agente Móvil

1. **La `contrasena` nunca regresa en la respuesta** — no esperes ese campo en el JSON de respuesta.
2. **El campo `id`** es generado automáticamente, guárdalo localmente para usar en PUT/PATCH/DELETE.
3. **`usuario` y `correo` son únicos** — maneja el error `422` para mostrar al usuario que ya están registrados.
4. **`created_at` y `updated_at`** vienen en formato ISO 8601 UTC (`2026-06-18T20:00:00.000000Z`).
5. **Para PATCH**, no es necesario enviar todos los campos, solo los que cambien.
6. **Siempre enviar** el header `Accept: application/json` para que Laravel devuelva errores en JSON y no en HTML.

# Guía: Manejo de Imágenes de Contactos — Android (Kotlin) ↔ Laravel API

## Contexto general

Las imágenes de contactos se guardan como archivos físicos en el servidor Laravel.
La app Android debe enviar el archivo usando `multipart/form-data` (NO JSON).
La API devuelve una **URL pública** (`https://...`) que la app puede usar directamente con Glide o Coil.

---

## Base URL

```
http://<IP_DEL_SERVIDOR>:8080/api
```

> ⚠️ En desarrollo local, usa la IP de tu máquina en la red (no `localhost`).
> Ejemplo: `http://192.168.1.100:8080/api`

---

## Endpoints de Imágenes

### 1. Subir una foto de contacto

```
POST /api/contacts/{contactId}/images
Content-Type: multipart/form-data
```

| Campo  | Tipo   | Requerido | Descripción                        |
|--------|--------|-----------|------------------------------------|
| imagen | File   | ✅ Sí     | Archivo de imagen (jpg, png, webp) |
| alt    | String | ❌ No     | Texto alternativo / descripción    |

**Restricciones del archivo:**
- Formatos aceptados: `jpeg`, `jpg`, `png`, `webp`
- Tamaño máximo: **5 MB**

**Respuesta exitosa (201):**
```json
{
  "success": true,
  "message": "Imagen agregada exitosamente",
  "data": {
    "id": 12,
    "url": "http://192.168.1.100:8080/storage/contactos/AbCdEfGh.jpg",
    "alt": "Foto de perfil"
  }
}
```

---

### 2. Listar imágenes de un contacto

```
GET /api/contacts/{contactId}/images
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "count": 2,
  "data": [
    { "id": 12, "url": "http://.../storage/contactos/foto1.jpg", "alt": "Perfil" },
    { "id": 13, "url": "http://.../storage/contactos/foto2.jpg", "alt": null }
  ]
}
```

---

### 3. Actualizar foto (reemplazar)

```
POST /api/images/{imageId}
Content-Type: multipart/form-data
```

> ⚠️ Retrofit no soporta `PATCH` con multipart. Usa `POST` y agrega el campo `_method=PATCH`.

| Campo   | Tipo   | Requerido | Descripción                       |
|---------|--------|-----------|-----------------------------------|
| _method | String | ✅ Sí     | Debe ser exactamente `"PATCH"`    |
| imagen  | File   | ❌ No     | Nueva imagen (reemplaza la vieja) |
| alt     | String | ❌ No     | Nuevo texto alternativo           |

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Imagen actualizada exitosamente",
  "data": { "id": 12, "url": "http://.../storage/contactos/nueva.jpg", "alt": "Nueva foto" }
}
```

---

### 4. Eliminar una imagen

```
DELETE /api/images/{imageId}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Imagen eliminada exitosamente"
}
```

---

## Implementación en Kotlin con Retrofit

### Dependencias necesarias (`build.gradle.kts`)

```kotlin
implementation("com.squareup.retrofit2:retrofit:2.9.0")
implementation("com.squareup.retrofit2:converter-gson:2.9.0")
implementation("com.squareup.okhttp3:okhttp:4.12.0")
implementation("com.squareup.okhttp3:logging-interceptor:4.12.0")

// Para mostrar imágenes desde URL
implementation("io.coil-kt:coil:2.6.0")
```

---

### Interface de Retrofit

```kotlin
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    // Subir imagen a un contacto
    @Multipart
    @POST("contacts/{contactId}/images")
    suspend fun subirImagen(
        @Path("contactId") contactId: Int,
        @Part imagen: MultipartBody.Part,
        @Part("alt") alt: RequestBody? = null
    ): Response<ImageResponse>

    // Listar imágenes de un contacto
    @GET("contacts/{contactId}/images")
    suspend fun listarImagenes(
        @Path("contactId") contactId: Int
    ): Response<ImageListResponse>

    // Actualizar imagen (usar POST + _method=PATCH)
    @Multipart
    @POST("images/{imageId}")
    suspend fun actualizarImagen(
        @Path("imageId") imageId: Int,
        @Part("_method") method: RequestBody,   // siempre "PATCH"
        @Part imagen: MultipartBody.Part? = null,
        @Part("alt") alt: RequestBody? = null
    ): Response<ImageResponse>

    // Eliminar imagen
    @DELETE("images/{imageId}")
    suspend fun eliminarImagen(
        @Path("imageId") imageId: Int
    ): Response<SuccessResponse>
}
```

---

### Data classes de respuesta

```kotlin
data class ImageResponse(
    val success: Boolean,
    val message: String?,
    val data: ImageData
)

data class ImageListResponse(
    val success: Boolean,
    val count: Int,
    val data: List<ImageData>
)

data class ImageData(
    val id: Int,
    val url: String,
    val alt: String?
)

data class SuccessResponse(
    val success: Boolean,
    val message: String
)
```

---

### Cómo subir una foto seleccionada por el usuario

```kotlin
import android.content.Context
import android.net.Uri
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.asRequestBody
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.File

// uri = resultado del photo picker / galería
fun uriToMultipart(context: Context, uri: Uri, fieldName: String = "imagen"): MultipartBody.Part {
    val inputStream = context.contentResolver.openInputStream(uri)!!
    val tempFile = File.createTempFile("img_", ".jpg", context.cacheDir)
    tempFile.outputStream().use { inputStream.copyTo(it) }

    val requestBody = tempFile.asRequestBody("image/jpeg".toMediaTypeOrNull())
    return MultipartBody.Part.createFormData(fieldName, tempFile.name, requestBody)
}

// Uso desde un ViewModel:
suspend fun subirFoto(context: Context, contactId: Int, uri: Uri) {
    val imagePart = uriToMultipart(context, uri)
    val altBody = "Foto de perfil".toRequestBody("text/plain".toMediaTypeOrNull())

    val response = apiService.subirImagen(contactId, imagePart, altBody)

    if (response.isSuccessful) {
        val urlGuardada = response.body()?.data?.url
        // Guardar urlGuardada en tu estado / LiveData / StateFlow
    }
}
```

---

### Mostrar la imagen con Coil

```kotlin
import coil.load
import coil.transform.CircleCropTransformation

// En tu Fragment o ViewHolder:
imageView.load(imageData.url) {
    crossfade(true)
    placeholder(R.drawable.ic_person_placeholder)
    error(R.drawable.ic_broken_image)
    transformations(CircleCropTransformation()) // opcional: foto circular
}
```

---

## Flujo completo paso a paso

```
1. Usuario toca "Cambiar foto" en la app
        ↓
2. Lanzar Photo Picker (ActivityResultLauncher)
        ↓
3. El usuario selecciona una foto de la galería
        ↓
4. Convertir la Uri a MultipartBody.Part  ← uriToMultipart()
        ↓
5. POST /api/contacts/{id}/images  (multipart/form-data)
        ↓
6. Laravel guarda el archivo en /storage/app/public/contactos/
        ↓
7. La API responde con { "data": { "url": "http://..." } }
        ↓
8. Mostrar la URL recibida con Coil en ImageView
```

---

## Errores comunes de la API

| Código | Causa                                   | Solución                                 |
|--------|-----------------------------------------|------------------------------------------|
| 422    | Campo `imagen` falta o nombre incorrecto | El campo DEBE llamarse exactamente `imagen` |
| 422    | Archivo muy grande (> 5 MB)             | Comprimir la imagen antes de enviar      |
| 422    | Formato no soportado                    | Solo jpeg, jpg, png, webp                |
| 404    | Contacto o imagen no encontrada         | Verificar el ID en la URL                |
| 500    | Error interno del servidor              | Verificar que `storage:link` esté hecho  |

---

## Nota: por qué no se usa JSON aquí

El endpoint de imágenes usa `multipart/form-data` porque:
- JSON no puede transportar archivos binarios (imágenes)
- `multipart/form-data` es el estándar para subir archivos en HTTP

El resto de endpoints de contactos (`POST /contacts`, `PATCH /contacts/{id}`, etc.)
siguen usando `Content-Type: application/json` normalmente.

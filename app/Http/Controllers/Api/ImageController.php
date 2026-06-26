<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ImageResource;
use App\Models\Contact;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Listar todas las imágenes de un contacto.
     *
     * GET /api/contacts/{contact}/images
     */
    public function index(Contact $contact)
    {
        $images = $contact->images()->latest()->get();

        return response()->json([
            'success' => true,
            'count'   => $images->count(),
            'data'    => ImageResource::collection($images),
        ], 200);
    }

    /**
     * Agregar una nueva imagen a un contacto.
     *
     * POST /api/contacts/{contact}/images
     * Content-Type: multipart/form-data
     * Campos: imagen (file), alt (string, opcional)
     */
    public function store(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'imagen' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'alt'    => 'nullable|string|max:150',
        ]);

        // Guarda el archivo en storage/app/public/contactos/
        $path = $request->file('imagen')->store('contactos', 'public');

        $image = $contact->images()->create([
            'url' => asset('storage/' . $path),
            'alt' => $validated['alt'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Imagen agregada exitosamente',
            'data'    => new ImageResource($image),
        ], 201);
    }

    /**
     * Ver una imagen específica de un contacto.
     *
     * GET /api/contacts/{contact}/images/{image}
     */
    public function show(Contact $contact, Image $image)
    {
        $this->authorizeImage($contact, $image);

        return response()->json([
            'success' => true,
            'data'    => new ImageResource($image),
        ], 200);
    }

    /**
     * Actualizar una imagen de un contacto.
     * Reemplaza el archivo físico anterior si se envía uno nuevo.
     *
     * PATCH /api/contacts/{contact}/images/{image}  (shallow)
     * Content-Type: multipart/form-data
     * Campos: imagen (file, opcional), alt (string, opcional)
     */
    public function update(Request $request, Contact $contact, Image $image)
    {
        $this->authorizeImage($contact, $image);

        $validated = $request->validate([
            'imagen' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:5120',
            'alt'    => 'sometimes|nullable|string|max:150',
        ]);

        if ($request->hasFile('imagen')) {
            // Eliminar el archivo físico anterior si existe en storage
            $this->deleteStorageFile($image->url);

            $path = $request->file('imagen')->store('contactos', 'public');
            $validated['url'] = asset('storage/' . $path);
        }

        unset($validated['imagen']); // no es columna de BD
        $image->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Imagen actualizada exitosamente',
            'data'    => new ImageResource($image),
        ], 200);
    }

    /**
     * Eliminar una imagen de un contacto.
     * También borra el archivo físico del storage.
     *
     * DELETE /api/contacts/{contact}/images/{image}  (shallow)
     */
    public function destroy(Contact $contact, Image $image)
    {
        $this->authorizeImage($contact, $image);

        $this->deleteStorageFile($image->url);
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Imagen eliminada exitosamente',
        ], 200);
    }

    /**
     * Verifica que la imagen pertenece al contacto dado.
     * Evita acceder a imágenes de otro contacto mediante la URL.
     */
    private function authorizeImage(Contact $contact, Image $image): void
    {
        if (
            $image->imageable_type !== Contact::class ||
            $image->imageable_id   !== $contact->id
        ) {
            abort(404, 'Imagen no encontrada para este contacto.');
        }
    }

    /**
     * Elimina el archivo físico del disco 'public' si la URL pertenece a este servidor.
     */
    private function deleteStorageFile(string $url): void
    {
        $base = asset('storage/');

        if (str_starts_with($url, $base)) {
            $relativePath = str_replace($base . '/', '', $url);
            Storage::disk('public')->delete($relativePath);
        }
    }
}

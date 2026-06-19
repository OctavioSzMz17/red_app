<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = Contact::with('images')->get();
        return response()->json([
            'success' => true,
            'count'   => $contacts->count(),
            'data'    => ContactResource::collection($contacts),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u'
            ],
            'numero' => [
                'required',
                'string',
                'max:15',
                'regex:/^[0-9]+$/'
            ],
            'usuario' => [
                'required',
                'string',
                'max:50',
                'alpha_num',
                'unique:contacts,usuario'
            ],
            'contrasena' => 'required|string|min:6|max:100',
            'correo' => [
                'required',
                'email',
                'max:100',
                'unique:contacts,correo'
            ],
        ]);

        $contact = Contact::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Contacto creado exitosamente',
            'data'    => new ContactResource($contact),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        $contact->load('images');
        return response()->json([
            'success' => true,
            'data'    => new ContactResource($contact),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u'
            ],
            'numero' => [
                'sometimes',
                'required',
                'string',
                'max:15',
                'regex:/^[0-9]+$/'
            ],
            'usuario' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'alpha_num',
                'unique:contacts,usuario,' . $contact->id
            ],
            'contrasena' => 'sometimes|required|string|min:6|max:100',
            'correo' => [
                'sometimes',
                'required',
                'email',
                'max:100',
                'unique:contacts,correo,' . $contact->id
            ],
        ]);

        $contact->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Contacto actualizado exitosamente',
            'data'    => new ContactResource($contact->load('images')),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contacto eliminado exitosamente',
        ], 200);
    }
}

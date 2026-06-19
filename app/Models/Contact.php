<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Contact extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\ContactFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'numero',
        'usuario',
        'contrasena',
        'correo',
        'email_verified_at',
    ];

    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'contrasena'        => 'hashed',
        ];
    }

    /**
     * Override: Laravel espera el campo 'password' por defecto.
     * Aquí apuntamos al campo 'contrasena' de esta tabla.
     */
    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    /**
     * Relación polimórfica: un Contact puede tener muchas imágenes.
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}

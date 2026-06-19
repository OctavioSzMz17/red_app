<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unifica users en contacts:
     * - Agrega email_verified_at y remember_token a contacts.
     * - Elimina la tabla users y password_reset_tokens.
     */
    public function up(): void
    {
        // 1. Agregar campos de autenticación a contacts
        Schema::table('contacts', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('correo');
            $table->rememberToken()->after('email_verified_at');
        });

        // 2. Eliminar tablas propias de users
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }

    /**
     * Rollback: restaura users y elimina los campos agregados a contacts.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['email_verified_at', 'remember_token']);
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }
};

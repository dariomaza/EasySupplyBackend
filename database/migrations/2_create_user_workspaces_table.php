<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('workspace_id');
            $table->boolean('mainUser');
            $table->timestamps();

            // Definir las restricciones de clave externa (foreign keys)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');

            // Asegurar que no hay duplicados en las combinaciones de user_id y workspace_id
            $table->unique(['user_id', 'workspace_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_workspaces');
    }
};

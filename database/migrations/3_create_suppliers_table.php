<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Clave primaria como UUID
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('order_method');
            $table->string('img_src')->nullable();
            $table->uuid('workspace_id'); // Clave foránea como UUID
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE suppliers MODIFY id CHAR(36) NOT NULL DEFAULT (UUID())");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};

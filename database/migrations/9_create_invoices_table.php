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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('path');
            $table->float('size');
            $table->string('workspace_id');
            $table->timestamps();
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE orders MODIFY id CHAR(36) NOT NULL DEFAULT (UUID())");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

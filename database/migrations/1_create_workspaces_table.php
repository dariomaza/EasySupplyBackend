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
        Schema::create('workspaces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("name");
            $table->string("street")->nullable();
            $table->string("city")->nullable();
            $table->string("zipCode")->nullable();
            $table->string("country")->nullable();
            $table->string("plan");
            $table->integer('maxUsers');
            $table->double('maxStorage'); //In MegaBytes
            $table->string('imgSrc')->nullable();
            $table->timestamps();

        });

        DB::statement("ALTER TABLE workspaces MODIFY id CHAR(36) NOT NULL DEFAULT (UUID())");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};

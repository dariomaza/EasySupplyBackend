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
        Schema::create('products_carts', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->string('cart_id');
            $table->integer('product_cuantity');
            $table->timestamps();

            // Definir las restricciones de clave externa (foreign keys)
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');


            $table->unique(['product_id', 'cart_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_carts');
    }
};

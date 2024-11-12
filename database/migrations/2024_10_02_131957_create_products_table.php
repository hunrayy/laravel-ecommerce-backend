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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('productName');
            $table->text('productImage');
            $table->string('subImage1')->nullable();
            $table->string('subImage2')->nullable();
            $table->string('subImage3')->nullable();
            $table->decimal('productPriceInNaira12Inches', 10, 2);
            $table->decimal('productPriceInNaira14Inches', 10, 2);
            $table->decimal('productPriceInNaira16Inches', 10, 2);
            $table->decimal('productPriceInNaira18Inches', 10, 2);
            $table->decimal('productPriceInNaira20Inches', 10, 2);
            $table->decimal('productPriceInNaira22Inches', 10, 2);
            $table->decimal('productPriceInNaira24Inches', 10, 2);
            $table->decimal('productPriceInNaira26Inches', 10, 2);
            $table->decimal('productPriceInNaira28Inches', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

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
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('productPriceInNaira12Inches', 'productPrice12Inches');
            $table->renameColumn('productPriceInNaira14Inches', 'productPrice14Inches');
            $table->renameColumn('productPriceInNaira16Inches', 'productPrice16Inches');
            $table->renameColumn('productPriceInNaira18Inches', 'productPrice18Inches');
            $table->renameColumn('productPriceInNaira20Inches', 'productPrice20Inches');
            $table->renameColumn('productPriceInNaira22Inches', 'productPrice22Inches');
            $table->renameColumn('productPriceInNaira24Inches', 'productPrice24Inches');
            $table->renameColumn('productPriceInNaira26Inches', 'productPrice26Inches');
            $table->renameColumn('productPriceInNaira28Inches', 'productPrice28Inches');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('productPrice12Inches', 'productPriceInNaira12Inches');
            $table->renameColumn('productPrice14Inches', 'productPriceInNaira14Inches');
            $table->renameColumn('productPrice16Inches', 'productPriceInNaira16Inches');
            $table->renameColumn('productPrice18Inches', 'productPriceInNaira18Inches');
            $table->renameColumn('productPrice20Inches', 'productPriceInNaira20Inches');
            $table->renameColumn('productPrice22Inches', 'productPriceInNaira22Inches');
            $table->renameColumn('productPrice24Inches', 'productPriceInNaira24Inches');
            $table->renameColumn('productPrice26Inches', 'productPriceInNaira26Inches');
            $table->renameColumn('productPrice28Inches', 'productPriceInNaira28Inches');
        });
    }
};

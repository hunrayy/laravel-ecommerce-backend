<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ProductsCategory;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products_category', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Bulk insert the categories
        ProductsCategory::insert([
            ['name' => 'Donor raw hair'],
            ['name' => 'Virgin hairs'],
            ['name' => 'Hair installation'],
            ['name' => 'Lash extensions'],
            ['name' => 'General'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_category');
    }
};

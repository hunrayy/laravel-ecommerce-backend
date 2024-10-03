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
        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->string('firstname');;
            $table->string('lastname');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('user');
            $table->string('is_an_admin');
            $table->string('countryOfWarehouseLocation');
            $table->string('domesticShippingFeeInNaira');
            $table->string('internationalShippingFeeInNaira');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};

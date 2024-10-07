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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tracking_id');
            $table->foreignId('user_id')->constrained('users');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email');
            $table->string('country');
            $table->string('state');
            $table->string('address');
            $table->string('city');
            $table->string('postalCode');
            $table->string('phoneNumber');
            $table->decimal('totalPrice', 10, 2);
            $table->string('currency');
            $table->json('products');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

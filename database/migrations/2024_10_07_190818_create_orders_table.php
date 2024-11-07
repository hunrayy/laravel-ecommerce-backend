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
            $table->string('transaction_id');
            $table->uuid('user_id'); // Use uuid for user_id to match the UUID type in users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Add foreign key constraint
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email');
            $table->string('country');
            $table->string('state');
            $table->string('address');
            $table->string('city');
            $table->string('postalCode')->nullable();
            $table->string('phoneNumber');
            $table->decimal('totalPrice', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shippingFee', 10, 2);
            $table->string('currency');
            $table->json('products');
            $table->string('expectedDateOfDelivery');
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

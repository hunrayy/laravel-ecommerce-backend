<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('firstname');;
            $table->string('lastname');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('user');
            $table->string('is_an_admin');
            $table->string('countryOfWarehouseLocation');
            $table->decimal('domesticShippingFeeInNaira', 10, 2);
            $table->decimal('internationalShippingFeeInNaira', 10, 2);
            $table->timestamps();
        });

        Admin::create([
            'id' => (string) Str::uuid(),
            'firstname' => 'john',
            'lastname' => 'doe',
            'email' => 'jhondoe@gmail.com',
            'password' => Hash::make('johndoe'),
            'user' => 'admin',
            'is_an_admin' => true,
            'countryOfWarehouseLocation' => 'Nigeria',
            'domesticShippingFeeInNaira' => 16551,
            'internationalShippingFeeInNaira' => 49653
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};

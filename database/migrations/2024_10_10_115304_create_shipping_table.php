<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Shipping;
use Illuminate\Support\Str;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipping', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('countryOfWarehouseLocation');
            $table->decimal('domesticShippingFeeInNaira', 10, 2);
            $table->decimal('internationalShippingFeeInNaira', 10, 2);
            $table->integer('numberOfDaysForDomesticDelivery');
            $table->integer('numberOfDaysForInternationalDelivery');
            $table->timestamps();
        });


        Shipping::create([
            'id' => (string) Str::uuid(),
            'countryOfWarehouseLocation' => 'Nigeria',
            'domesticShippingFeeInNaira' => 16551,
            'internationalShippingFeeInNaira' => 49653,
            'numberOfDaysForDomesticDelivery' => 7,
            'numberOfDaysForInternationalDelivery' => 14
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping');
    }
};

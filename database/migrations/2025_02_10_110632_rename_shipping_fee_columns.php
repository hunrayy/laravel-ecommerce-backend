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
            Schema::table('shipping', function (Blueprint $table) {
                $table->renameColumn('domesticShippingFeeInNaira', 'domesticShippingFee');
                $table->renameColumn('internationalShippingFeeInNaira', 'internationalShippingFee');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('shipping', function (Blueprint $table) {
                $table->renameColumn('domesticShippingFee', 'domesticShippingFeeInNaira');
                $table->renameColumn('internationalShippingFee', 'internationalShippingFeeInNaira');
            });
        }
    };

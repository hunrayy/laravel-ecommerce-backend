<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Pages;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('page');
            $table->text('firstSection');
            $table->text('secondSection');
            $table->text('thirdSection');
            $table->text('fourthSection')->nullable();
            $table->text('fifthSection')->nullable();
            $table->text('sixthSection')->nullable();
            $table->text('seventhSection')->nullable();
            $table->text('eighthSection')->nullable();
            $table->text('ninthSection')->nullable();
            $table->text('tenthSection')->nullable();
            $table->text('eleventhSection')->nullable();
            $table->text('twelfthSection')->nullable();

            $table->timestamps();
        });

        Pages::insert([
            [
                'id' => (string) Str::uuid(),
                'title' => 'Shipping policy',
                'page' => 'shippingPolicy',
                'firstSection' => 'International customers, please be aware of any custom duties or fees that may apply to your order upon reaching your country. We cannot be held responsible for clearing your order or paying the customs fee if any.',
                'secondSection' => 'If you happen to miss your delivery, the courier usually attempts delivery for three consecutive days. If you miss these attempts, please contact us to rearrange shipping. In the event that your tracking info says "returned to sender", there may be an additional shipping fee any.',
                'thirdSection' => 'Shipping itself is always express/next-day service. However, please keep in mind that there is a standard processing duration of 2-3 working days on average for our ready-to-ship items. For custom orders and sale orders, the processing duration is typically 4-7 working days.',
                'fourthSection' => null,  
                'fifthSection' => null,    
                'sixthSection' => null,     
                'seventhSection' => null,   
                'eighthSection' => null,
                'ninthSection' => null,
                'tenthSection' => null,
                'eleventhSection' => null,
                'twelfthSection' => null,

            ],
            [
                'id' => (string) Str::uuid(),
                'title' => 'Refund policy',
                'page' => 'refundPolicy',
                'firstSection' => 'All sales are final, and we don\'t offer refunds or returns as our products bespoke items and are made to order.',
                'secondSection' => 'However, you are entitled to a refund or exchange   - if you receive the wrong item.',
                'thirdSection' => '- If your order was shipped to a different address   and not what you filled out. Please contact if you’d like to change your address.',
                'fourthSection' => '-In case your order is delayed beyond the 10 working days processing duration during peak times, you can request a refund.',
                'fifthSection' => '- Please note that we cannot provide a refund if the delay is due to the courier service, as we have limited control over that situation.',
                'sixthSection' => '- Returns and refunds are only accepted under rare circumstances, but please be aware that if you return an order that has been installed, used, lace cut, or passed 7 days since declared delivered, you will not be entitled to a refund.',
                'seventhSection' => 'Refunds could take up to 10 working days to reflect',
                'eighthSection' => '- If your order was shipped to a different address   and not what you filled out. Please contact if you’d like to change your address.',
                'ninthSection' => null,
                'tenthSection' => null,
                'eleventhSection' => null,
                'twelfthSection' => null,
            ],
            [
                'id' => (string) Str::uuid(),
                'title' => 'Delivery policy',
                'page' => 'deliveryPolicy',
                'firstSection' => 'Our delivery charges vary based on your location and the total weight of your order. The exact delivery cost will be calculated at checkout.',
                'secondSection' => 'We aim to process and dispatch all orders within 2-3 working days. During peak times or sales, processing may take longer.',
                'thirdSection' => 'Delivery times depend on your location:',
                'fourthSection' => 'Local deliveries (within the city): 1-3 working days',
                'fifthSection' => 'National deliveries: 3-7 working days',
                'sixthSection' => 'International deliveries: 7-21 working days',
                'seventhSection' => 'Once your order has been dispatched, you will receive a shipping confirmation email with a tracking number. You can use this to track the status of your delivery.',
                'eighthSection' => 'We do not offer free delivery. All delivery charges will be clearly stated at checkout before you complete your purchase.',
                "ninthSection" => 'Please ensure that the delivery address provided is accurate and complete. We are not responsible for any delays or misdeliveries due to incorrect or incomplete addresses.',
                'tenthSection' => 'If you need to change your delivery address after placing an order, please contact us as soon as possible. Changes to the delivery address cannot be made once the order has been dispatched.',
                'eleventhSection' => 'In case of any delays or issues with delivery, please contact our customer service team for assistance. We will do our best to resolve any problems as quickly as possible.',
                'twelfthSection' => 'By placing an order with us, you agree to our delivery policy and understand the delivery times and charges applicable to your order.'
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};

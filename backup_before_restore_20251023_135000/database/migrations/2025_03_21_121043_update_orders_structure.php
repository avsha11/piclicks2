<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop the old orders table if it exists
        Schema::dropIfExists('orders');

        // Create new orders table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('internal_order_id')->unique();
            $table->string('order_status');
            $table->integer('total_quantity');
            $table->double('total_amount', 10, 2);
            $table->double('frame_amount', 10, 2)->nullable();
            $table->double('shipping_amount', 10, 2)->nullable();
            $table->double('vat_amount', 10, 2)->nullable();

            // Shipping details
            $table->string('shipping_fullname');
            $table->string('shipping_phone');
            $table->string('shipping_email');
            $table->string('shipping_company_name')->nullable();
            $table->string('shipping_address');
            $table->string('shipping_address_opt')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_postalcode')->nullable();
            $table->string('shipping_state');
            $table->string('shipping_country');

            // Billing details
            $table->string('billing_fullname')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_address_opt')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_postal')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_country')->nullable();

            $table->date('delivery_date')->nullable();
            $table->longText('order_tracking')->nullable();

            $table->timestamps();
        });

        // Create order_detail table
        Schema::create('order_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_id')->index();
            $table->string('internal_order_id')->index();
            $table->unsignedBigInteger('collage_unique_id')->index();
            $table->integer('quantity');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });

        // Create transaction table
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('internal_order_id')->index();
            $table->string('transaction_id')->unique();
            $table->string('payment_gateway');
            $table->decimal('total_amount', 10, 2);
            $table->text('other')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction');
        Schema::dropIfExists('order_detail');
        Schema::dropIfExists('orders');
    }
};

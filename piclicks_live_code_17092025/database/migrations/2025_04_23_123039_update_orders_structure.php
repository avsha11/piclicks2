<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->double('sub_total',10,2)->after('total_amount')->nullable();
            $table->double('discount_amount',10,2)->after('vat_amount')->nullable();
            $table->string('coupon_code')->after('discount_amount')->nullable();
            $table->double('giftcard_amount',10,2)->after('coupon_code')->nullable();
            $table->string('giftcard_code', 1000)->after('giftcard_amount')->nullable();
        });

        // Rename 'amount' to 'price' in 'order_detail'
        Schema::table('order_detail', function (Blueprint $table) {
            $table->string('giftcard_type')->after('collage_unique_id');
            $table->unsignedBigInteger('price_id')->after('collage_unique_id');
            $table->string('name')->after('giftcard_type');
        });


        // Create 'order_giftcard' table
        Schema::create('order_giftcard', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_id')->index();
            $table->string('type');
            $table->double('price');
            $table->integer('quantity');
            $table->double('total_amt');
            $table->string('name');
            $table->string('email');
            $table->text('message')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=not used, 1=used');
            $table->unsignedBigInteger('used_on_order_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('sub_total');
            $table->dropColumn('discount_amount');
            $table->dropColumn('coupon_code');
            $table->dropColumn('giftcard_amount');
            $table->dropColumn('giftcard_code');
        });

        // Revert column rename
        Schema::table('order_detail', function (Blueprint $table) {
            $table->dropColumn('name');
        });


        // Drop 'order_giftcard' table
        Schema::dropIfExists('order_giftcard');
    }
};

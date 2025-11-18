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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('coupon_code')->unique();
            $table->text('description')->nullable();
            $table->integer('discount');
            $table->integer('usage_count')->default(0);
            $table->decimal('min_purchase_amount', 10, 2)->nullable();
            $table->dateTime('valid_from');
            $table->dateTime('valid_to');
            $table->integer('limit_used')->default(0);
            $table->boolean('status')->default(1)->comment('0 = inactive, 1 = active');
            $table->string('discount_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};

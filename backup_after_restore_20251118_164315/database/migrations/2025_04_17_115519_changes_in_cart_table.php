<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename 'name' column to 'type' and add new columns to carts table
        Schema::table('carts', function (Blueprint $table) {
            // $table->renameColumn('name', 'item_type');
            $table->string('giftcard_name')->nullable()->after('updated_at');
            $table->string('email')->nullable()->after('giftcard_name');
            $table->text('message')->nullable()->after('email');
        });

        // Drop gift_card_cart table
        Schema::dropIfExists('gift_card_cart');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename 'type' column back to 'name' and drop new columns from carts table
        Schema::table('carts', function (Blueprint $table) {
            // $table->renameColumn('type', 'name');
            $table->dropColumn(['email', 'message', 'giftcard_name']);
        });

        // Recreate gift_card_cart table
        Schema::create('gift_card_cart', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('design_type')->nullable();
            $table->string('email')->nullable();
            $table->integer('cost')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
            // Add other original columns here if needed
        });
    }
};

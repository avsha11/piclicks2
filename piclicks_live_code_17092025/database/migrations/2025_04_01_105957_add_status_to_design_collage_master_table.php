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
        Schema::table('design_collage_master', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->comment('1: odered, 0: draft')->after('image_path');
            $table->string('user_type')->nullable()->after('status')->comment('user_type deffine where the collage is created from admin or user');
            $table->bigInteger('price_id')->after('unique_id');
            $table->bigInteger('artgallery_unique_id')->nullable()->after('user_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_collage_master', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('user_type');
            $table->dropColumn('price_id');
            $table->dropColumn('artgallery_unique_id');
        });
    }
};

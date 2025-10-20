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
        Schema::table('design_collage', function (Blueprint $table) {
            $table->string('image_with_bleed')->nullable()->after('image_edited');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_collage', function (Blueprint $table) {
            $table->dropColumn('image_with_bleed');
        });
    }
};

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
        Schema::create('design_collage', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->index('unique_id');
            $table->string('image')->nullable();
            $table->integer('seq', 5);
            $table->tinyInteger('empty', 4)->default(0);
            $table->text('other_settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_collage');
    }
};

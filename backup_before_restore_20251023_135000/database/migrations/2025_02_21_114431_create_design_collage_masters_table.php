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
        Schema::create('design_collage_master', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('unique_id');
            $table->integer('grid_rows')->default(0);
            $table->integer('grid_columns')->default(0);
            $table->double('width')->default(0);
            $table->double('height')->default(0);
            $table->integer('total_tiles')->default(0)->nullable();
            $table->integer('frame')->default(0);
            $table->string('filter')->nullable();
            $table->longText('text_editor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_collage_master');
    }
};

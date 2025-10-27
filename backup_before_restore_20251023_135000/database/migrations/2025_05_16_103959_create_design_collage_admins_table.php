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
        Schema::create('design_collage_admins', function (Blueprint $table) {
            $table->id();
           $table->string('unique_id')->unique();
            $table->json('tag_id')->nullable(); // Store multiple tag IDs as JSON
            $table->unsignedBigInteger('collection_id');
            $table->string('title');
            $table->text('short_description')->nullable();
            $table->string('designer_name')->nullable();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_collage_admins');
    }
};

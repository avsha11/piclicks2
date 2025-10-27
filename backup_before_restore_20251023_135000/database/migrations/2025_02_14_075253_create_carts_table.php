<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('product_id');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->bigInteger('frame')->nullable();
            $table->string('size_vert')->nullable();
            $table->string('size_horiz')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('carts');
    }
};

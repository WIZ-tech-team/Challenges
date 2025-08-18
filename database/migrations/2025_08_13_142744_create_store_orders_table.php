<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_user_id')->constrained('api_users')->onDelete('cascade');
            $table->foreignId('store_product_id')->constrained('store_products')->onDelete('cascade');
            $table->double('points');
            $table->enum('status', ['pending', 'approved', 'not-approved', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_orders');
    }
};

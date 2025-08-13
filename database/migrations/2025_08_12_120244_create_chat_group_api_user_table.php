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
        Schema::create('chat_group_api_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_group_id')->constrained('chat_groups')->onDelete('cascade');
            $table->foreignId('api_user_id')->constrained('api_users')->onDelete('cascade');
            $table->enum('role', ['member', 'admin'])->default('member');
            // $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['chat_group_id', 'api_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_group_api_user');
    }
};

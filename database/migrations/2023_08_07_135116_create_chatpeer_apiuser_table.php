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
        Schema::create('chatpeer_apiuser', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_peer_id')->constrained('chat_peers')->cascadeOnDelete();
            $table->foreignId('participants_id')->constrained('api_users')->cascadeOnDelete();
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
        Schema::dropIfExists('chatpeer_apiuser');
    }
};

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
        Schema::create('chat_peers', function (Blueprint $table) {
            $table->id();
            $table->string('lastmessageId')->nullable();
            $table->foreignId('created_by')->constrained('api_users','id')->cascadeOnDelete();
            $table->enum('type',['peer','group'])->default('peer');
            $table->string('chat_id')->nullable();
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
        Schema::dropIfExists('chat_peers');
    }
};

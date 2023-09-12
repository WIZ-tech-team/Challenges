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
        Schema::create('challenges_api_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->nullable()->constrained('challenges','id')->cascadeOnDelete();
            $table->foreignId('users_id')->nullable()->constrained('api_users','id')->cascadeOnDelete();

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
        Schema::dropIfExists('challenges_api_users');
    }
};

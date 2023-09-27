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
        Schema::create('footballcylics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('challenges','id')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams','id')->cascadeOnDelete();
            $table->integer('cylicNum')->nullable();
            $table->string('result')->nullable();
            $table->foreignId('winner_team')->nullable()->constrained('teams','id')->cascadeOnDelete();
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
        Schema::dropIfExists('footballcylics');
    }
};

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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['public','private'])->default('private');
            $table->decimal('latitude')->nullable();
            $table->decimal('longitude')->nullable();
            $table->foreignId('category_id')->constrained('categories','id')->cascadeOnDelete();
            $table->foreignId('refree_id')->constrained('api_users','id')->cascadeOnDelete()->nullable();
            $table->foreignId('team_id')->constrained('teams','id')->cascadeOnDelete()->nullable();
            $table->datetime('start-time')->nullabel();
            $table->datetime('end-time')->nullabel();
            $table->date('date')->nullabel();
            $table->string('distance')->nullable();
            $table->string('stepsNum')->nullable();
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
        Schema::dropIfExists('challenges');
     
    }
};

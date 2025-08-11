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
        Schema::create('football_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_1_id')->constrained('teams')->onDelete('cascade');
            $table->integer('team_1_score');
            $table->foreignId('team_1_award_id')->nullable()->constrained('awards')->onDelete('set null');
            $table->integer('team_1_points')->default(0);
            $table->foreignId('team_2_id')->constrained('teams')->onDelete('cascade');
            $table->integer('team_2_score');
            $table->foreignId('team_2_award_id')->nullable()->constrained('awards')->onDelete('set null');
            $table->integer('team_2_points')->default(0);
            $table->timestamps();
            $table->unique('challenge_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('football_results');
    }
};

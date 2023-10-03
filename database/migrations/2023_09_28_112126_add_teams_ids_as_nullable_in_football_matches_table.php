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
        Schema::table('football_matches', function (Blueprint $table) {
            $table->foreignId('team1_id')->nullable()->constrained('teams','id')->cascadeOnDelete();
            $table->foreignId('team2_id')->nullable()->constrained('teams','id')->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('football_matches', function (Blueprint $table) {
           $table->dropColumn('team1_id');
           $table->dropColumn('team2_id');
        });
    }
};

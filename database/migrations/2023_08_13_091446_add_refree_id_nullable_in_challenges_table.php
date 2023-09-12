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
        Schema::table('challenges', function (Blueprint $table) {
            $table->foreignId('refree_id')->nullable()->after('longitude' )->constrained('api_users','id')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->after('longitude' )->constrained('teams','id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('refree_id');
            $table->dropColumn('team_id');
        });
    }
};

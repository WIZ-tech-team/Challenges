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
        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('lead_by')->nullable()->after('category')->constrained('api_users')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->after('lead_by')->constrained('api_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['lead_by']);
            $table->dropColumn('lead_by');
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};

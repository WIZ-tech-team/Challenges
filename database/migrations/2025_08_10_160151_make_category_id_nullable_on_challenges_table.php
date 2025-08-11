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
            // Drop the existing foreign key constraint
            $table->dropForeign(['category_id']);

            // Make category_id nullable
            $table->unsignedBigInteger('category_id')->nullable()->change();

            // Re-add the foreign key constraint with nullable support
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
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
            // Drop the existing foreign key constraint
            $table->dropForeign(['category_id']);

            // Make category_id nullable
            $table->unsignedBigInteger('category_id')->nullable()->change();

            // Re-add the foreign key constraint with nullable support
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }
};

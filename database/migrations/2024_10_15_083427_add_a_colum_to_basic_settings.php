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

        Schema::table('basic_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('basic_settings', 'time_format')) {
                $table->integer('time_format')->default(12)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            if (Schema::hasColumn('basic_settings', 'time_format')) {
                $table->dropColumn('time_format');
            }
        });
    }
};

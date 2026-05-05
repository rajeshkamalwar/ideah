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
            if (!Schema::hasColumn('basic_settings', 'google_map_api_key_status')) {
                $table->integer('google_map_api_key_status')->default(0)->nullable();
            }
            if (!Schema::hasColumn('basic_settings', 'google_map_api_key')) {
                $table->string('google_map_api_key')->nullable(); 
            }
            if (!Schema::hasColumn('basic_settings', 'radius')) {
                $table->integer('radius')->default(0)->nullable();
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
            if (Schema::hasColumn('basic_settings', 'google_map_api_key_status')) {
                $table->dropColumn('google_map_api_key_status');
            }
            if (Schema::hasColumn('basic_settings', 'google_map_api_key')) {
                $table->dropColumn('google_map_api_key');
            }
            if (Schema::hasColumn('basic_settings', 'radius')) {
                $table->dropColumn('radius');
            }
        });
    }

};

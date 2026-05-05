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
            $table->string('app_logo')->nullable();
            $table->string('app_fav')->nullable();
            $table->string('app_url')->nullable();
            $table->string('app_primary_color')->nullable();
            $table->string('app_breadcrumb_color')->nullable();
            $table->decimal('app_breadcrumb_overlay_opacity',8,2)->default(0.00);
            $table->tinyInteger('app_google_map_status')->default(0);
            $table->string('app_firebase_json_file')->nullable();
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
            //
        });
    }
};

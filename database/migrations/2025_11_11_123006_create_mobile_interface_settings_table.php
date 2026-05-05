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
        Schema::create('mobile_interface_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('language_id');
            $table->string('category_listing_section_title')->nullable();
            $table->string('featured_listing_section_title')->nullable();
            $table->string('banner_background_image')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('banner_title')->nullable();
            $table->string('banner_button_text')->nullable();
            $table->string('banner_button_url')->nullable();
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
        Schema::dropIfExists('mobile_interface_settings');
    }
};

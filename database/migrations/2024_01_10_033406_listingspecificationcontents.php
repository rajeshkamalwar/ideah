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
        Schema::create('listing_feature_contents', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('listing_feature_id')->nullable();
            $table->bigInteger('language_id')->nullable();
            $table->text('feature_heading')->nullable();
            $table->text('feature_value')->nullable();

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
        Schema::dropIfExists('listing_feature_contents');
    }
};

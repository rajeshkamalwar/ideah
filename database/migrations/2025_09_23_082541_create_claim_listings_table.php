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
        Schema::create('claim_listings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('language_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('information')->nullable();
            $table->string('redemption_token', 64)->nullable();
            $table->string('raw_redemption_token', 255)->nullable();
            $table->timestamp('redemption_expires_at')->nullable();
            $table->timestamp('approved_at')->nullable();
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
        Schema::dropIfExists('claim_listings');
    }
};

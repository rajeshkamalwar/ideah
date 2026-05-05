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
        Schema::create('withdraw_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->double('min_limit', 12, 2)->nullable();
            $table->double('max_limit', 12, 2)->nullable();
            $table->string('name', 255)->nullable();
            $table->integer('status')->default(1);
            $table->float('fixed_charge', 12, 2)->default(0.00);
            $table->float('percentage_charge', 12, 2)->default(0.00);
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
        Schema::dropIfExists('withdraw_payment_methods');
    }
};

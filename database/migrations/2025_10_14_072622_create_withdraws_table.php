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
        Schema::create('withdraws', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable(); 
            $table->string('withdraw_id', 255)->nullable();
            $table->integer('method_id')->nullable();
            $table->string('amount', 255)->nullable();
            $table->float('payable_amount', 8, 2)->default(0.00);
            $table->float('total_charge', 8, 2)->default(0.00);
            $table->text('additional_reference')->nullable();
            $table->text('feilds')->nullable(); ;
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('withdraws');
    }
};

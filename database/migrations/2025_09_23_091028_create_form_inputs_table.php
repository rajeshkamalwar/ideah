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
        Schema::create('form_inputs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id')->nullable();
            $table->tinyInteger('type')->default(1)->comment(
                "1 - Text Field\n" .
                    "2 - Number Field\n" .
                    "3 - Select\n" .
                    "4 - Checkbox\n" .
                    "5 - Textarea Field\n" .
                    "6 - Datepicker\n" .
                    "7 - Timepicker\n" .
                    "8 - File"
            );

            // Display label for the input field
            $table->string('label');
            $table->string('placeholder')->nullable();
            $table->string('name');
            $table->boolean('is_required')->default(0)->comment(
                "0 - not required\n" .
                    "1 - required"
            );

            $table->text('options')->nullable();
            $table->integer('file_size')->nullable();

            /**
             * Order number for sorting with comment
             */
            $table->integer('order_no')->default(0)->comment(
                "Order number for sorting\n" .
                    "default value 0 means, this input field has created just now and it has not sorted yet"
            );
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
        Schema::dropIfExists('form_inputs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_category_faq_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_category_id');
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('serial_number')->default(0);
            $table->timestamps();

            $table->foreign('listing_category_id')
                ->references('id')
                ->on('listing_categories')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_category_faq_templates');
    }
};

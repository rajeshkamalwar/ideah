<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('image', 255)->nullable();
            $table->dateTime('event_start');
            $table->dateTime('event_end')->nullable();
            $table->string('location', 500)->nullable();
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Published, 0=Draft');
            $table->unsignedInteger('serial_number')->default(0);
            $table->timestamps();
        });

        Schema::create('event_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255);
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->unique(['language_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_contents');
        Schema::dropIfExists('events');
    }
};

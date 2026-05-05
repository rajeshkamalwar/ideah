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
        Schema::table('feature_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('feature_orders', 'conversation_id')) {
                $table->string('conversation_id')->nullable();
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
        Schema::table('feature_orders', function (Blueprint $table) {
            if (Schema::hasColumn('feature_orders', 'conversation_id')) {
                $table->dropColumn('conversation_id');
            }
        });
    }
};

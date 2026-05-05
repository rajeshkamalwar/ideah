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
        Schema::table('claim_listings', function (Blueprint $table) {
            $table->string('redemption_token', 64)->nullable()->after('status');      // sha256 hex = 64 chars
            $table->timestamp('redemption_expires_at')->nullable()->after('redemption_token');
            $table->timestamp('approved_at')->nullable()->after('redemption_expires_at');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_listings', function (Blueprint $table) {
            $table->dropColumn(['redemption_token', 'redemption_expires_at', 'approved_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional per-listing package override (admin). When set, limits/features use this package instead of the vendor's active membership.
     */
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable()->after('vendor_id');
            $table->foreign('package_id')->references('id')->on('packages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn('package_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Removes is_active if a previous local migration added it (reverted feature).
     */
    public function up(): void
    {
        if (Schema::hasColumn('languages', 'is_active')) {
            Schema::table('languages', function (Blueprint $table): void {
                $table->dropColumn('is_active');
            });
        }
    }

    public function down(): void
    {
        //
    }
};

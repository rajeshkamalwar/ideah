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
        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table) {
                if (!Schema::hasColumns('admins', ['code', 'lang_code'])) {
                    $table->string('code')->nullable();
                    $table->string('lang_code')->nullable();
                }
            });
        }

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (!Schema::hasColumns('vendors', ['code', 'lang_code'])) {
                    $table->string('code')->nullable();
                    $table->string('lang_code')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table) {
                if (Schema::hasColumn('admins', 'code')) {
                    $table->dropColumn('code');
                }
                if (Schema::hasColumn('admins', 'lang_code')) {
                    $table->dropColumn('lang_code');
                }
            });
        }

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (Schema::hasColumn('vendors', 'code')) {
                    $table->dropColumn('code');
                }
                if (Schema::hasColumn('vendors', 'lang_code')) {
                    $table->dropColumn('lang_code');
                }
            });
        }
    }
};

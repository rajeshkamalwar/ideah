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
        Schema::table('product_contents', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['product_category_id']);

            // Drop the index associated with the foreign key
            $table->dropIndex('product_contents_product_category_id_foreign');

            // Make product_category_id nullable
            $table->unsignedBigInteger('product_category_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_contents', function (Blueprint $table) {

            $table->unsignedBigInteger('product_category_id')->nullable(false)->change();

            // Restore the index
            $table->index('product_category_id', 'product_contents_product_category_id_foreign');

            // Restore the foreign key constraint
            $table->foreign('product_category_id', 'product_contents_product_category_id_foreign')
                ->references('id')->on('product_categories')
                ->onDelete('restrict');
        });
    }
};

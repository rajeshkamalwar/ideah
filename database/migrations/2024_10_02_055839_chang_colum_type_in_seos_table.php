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
        Schema::table('seos', function (Blueprint $table) {
            $table->longText('meta_keyword_home')->change();
            $table->longText('meta_keyword_products')->change();
            $table->longText('meta_keyword_blog')->change();
            $table->longText('meta_keyword_faq')->change();
            $table->longText('meta_keyword_contact')->change();
            $table->longText('meta_keyword_login')->change();
            $table->longText('meta_keyword_signup')->change();
            $table->longText('meta_keyword_forget_password')->change();
            $table->longText('meta_keywords_vendor_login')->change();
            $table->longText('meta_description_vendor_login')->change();
            $table->longText('meta_keywords_vendor_signup')->change();
            $table->longText('meta_description_vendor_signup')->change();
            $table->longText('meta_keywords_vendor_forget_password')->change();
            $table->longText('meta_descriptions_vendor_forget_password')->change();
            $table->longText('meta_keywords_vendor_page')->change();
            $table->longText('meta_description_vendor_page')->change();
            $table->text('meta_keywords_about_page')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('seos', function (Blueprint $table) {

            $table->string('meta_keyword_home')->change();
            $table->string('meta_keyword_products')->change();
            $table->string('meta_keyword_blog')->change();
            $table->string('meta_keyword_faq')->change();
            $table->string('meta_keyword_contact')->change();
            $table->string('meta_keyword_login')->change();
            $table->string('meta_keyword_signup')->change();
            $table->string('meta_keyword_forget_password')->change();
            $table->string('meta_keywords_vendor_login')->change();
            $table->string('meta_description_vendor_login')->change();
            $table->string('meta_keywords_vendor_signup')->change();
            $table->string('meta_description_vendor_signup')->change();
            $table->string('meta_keywords_vendor_forget_password')->change();
            $table->string('meta_descriptions_vendor_forget_password')->change();
            $table->string('meta_keywords_vendor_page')->change();
            $table->string('meta_description_vendor_page')->change();
            $table->string('meta_keywords_about_page')->change();
        });
    }
};

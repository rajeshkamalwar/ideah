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
    Schema::table('online_gateways', function (Blueprint $table) {
      $table->tinyInteger('mobile_status')->default(0);
      $table->longText('mobile_information')->nullable();
    });

    DB::table('online_gateways')->insert([
      'name' => 'Authorize.net',
      'keyword' => 'authorize.net',
      'information' => "",
      'status' => 0,
      'mobile_status' => 0,
      'mobile_information' => ""
    ]);

    DB::table('online_gateways')->insert([
      'name' => 'Monnify',
      'keyword' => 'monnify',
      'information' => "",
      'status' => 0,
      'mobile_status' => 0,
      'mobile_information' => ""
    ]);
    DB::table('online_gateways')->insert([
      'name' => 'NowPayments',
      'keyword' => 'now_payments',
      'information' => "",
      'status' => 0,
      'mobile_status' => 0,
      'mobile_information' => ""
    ]);
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('online_gateways', function (Blueprint $table) {
      //
    });
  }
};

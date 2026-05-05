<?php

use App\Models\PaymentGateway\OnlineGateway;
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
            /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            --------- phonepe -----------------
            ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
            $phonepe = OnlineGateway::where('keyword', 'phonepe')->first();
            if (empty($phonepe)) {
                $phonepe = new OnlineGateway();
                $phonepe->status = 0;
                $phonepe->name = 'PhonePe';
                $phonepe->keyword = 'phonepe';

                $information = [];
                $information['merchant_id'] = null;
                $information['salt_key'] = null;
                $information['salt_index'] = 1;
                $information['sandbox_check'] = 1;
                $information['sandbox_status'] = 1;
                $information['text'] = "Pay via your PhonePe account.";

                $phonepe->information = json_encode($information);
                $phonepe->save();
            }

            /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            --------- Perfect Money -----------------
            ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
            $perfect_money = OnlineGateway::where('keyword', 'perfect_money')->first();
            if (empty($perfect_money)) {
                $information = [
                    'perfect_money_wallet_id' => null
                ];
                $perfect_money_info = [
                    'name' => 'Perfect Money',
                    'keyword' => 'perfect_money',
                    'information' => json_encode($information, true),
                    'status' => 0
                ];
                OnlineGateway::create($perfect_money_info);
            }
            /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            --------- Xendit -----------------
            ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
            $xendit = OnlineGateway::where('keyword', 'xendit')->first();
            if (empty($xendit)) {
                $information = [
                    'secret_key' => null
                ];
                $xendit_info = [
                    'name' => 'Xendit',
                    'keyword' => 'xendit',
                    'information' => json_encode($information, true),
                    'status' => 0
                ];
                OnlineGateway::create($xendit_info);
            }

            /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            --------- Myfatoorah -----------------
            ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
            $myfatoorah = OnlineGateway::where('keyword', 'myfatoorah')->first();
            if (empty($myfatoorah)) {
                $information = [
                    'sandbox_status' => null,
                    'token' => null
                ];
                $myfatoorah = [
                    'name' => 'Myfatoorah',
                    'keyword' => 'myfatoorah',
                    'information' => json_encode($information, true),
                    'status' => 0
                ];
                OnlineGateway::create($myfatoorah);
            }

            /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            --------- Yoco -----------------
            ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
            $yoco = OnlineGateway::where('keyword', 'yoco')->first();
            if (empty($yoco)) {
                $information = [
                    'secret_key' => null
                ];
                $yoco = [
                    'name' => 'Yoco',
                    'keyword' => 'yoco',
                    'information' => json_encode($information, true),
                    'status' => 0
                ];
                OnlineGateway::create($yoco);
            }

            /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            --------- toyyibpay -----------------
            ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
            $toyyibpay = OnlineGateway::where('keyword', 'toyyibpay')->first();
            if (empty($toyyibpay)) {
                $information = [
                    'sandbox_status' => null,
                    'secret_key' => null,
                    'category_code' => null
                ];
                $toyyibpay = [
                    'name' => 'Toyyibpay',
                    'keyword' => 'toyyibpay',
                    'information' => json_encode($information, true),
                    'status' => 0
                ];
                OnlineGateway::create($toyyibpay);
            }

            /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            --------- paytabs -----------------
            ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
            $paytabs = OnlineGateway::where('keyword', 'paytabs')->first();
            if (empty($paytabs)) {
                $information = [
                    'profile_id' => null,
                    'server_key' => null,
                    'api_endpoint' => null,
                    'country' => null
                ];
                $paytabs = [
                    'name' => 'Paytabs',
                    'keyword' => 'paytabs',
                    'information' => json_encode($information, true),
                    'status' => 0
                ];
                OnlineGateway::create($paytabs);
            }

            /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            --------- iyzico -----------------
            ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
            $iyzico = OnlineGateway::where('keyword', 'iyzico')->first();
            if (empty($iyzico)) {
                $information = [
                    'api_key' => null,
                    'secret_key' => null,
                    'iyzico_mode' => 0,
                    'sandbox_status' => null
                ];
                $iyzico = [
                    'name' => 'Iyzico',
                    'keyword' => 'iyzico',
                    'information' => json_encode($information, true),
                    'status' => 0
                ];
                OnlineGateway::create($iyzico);
            }

            /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            --------- midtrans -----------------
            ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
            $midtrans = OnlineGateway::where('keyword', 'midtrans')->first();
            if (empty($midtrans)) {
                $information = [
                    'server_key' => null,
                    'midtrans_mode' => 0,
                    'is_production' => null
                ];
                $midtrans = [
                    'name' => 'Midtrans',
                    'keyword' => 'midtrans',
                    'information' => json_encode($information, true),
                    'status' => 0
                ];
                OnlineGateway::create($midtrans);
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
        Schema::table('online_gateways', function (Blueprint $table) {
            $data = OnlineGateway::where('keyword', 'perfect_money')->first();
            if ($data) {
                $data->delete();
            }

            $xendit = OnlineGateway::where('keyword', 'xendit')->first();
            if ($xendit) {
                $xendit->delete();
            }

            $myfatoorah = OnlineGateway::where('keyword', 'myfatoorah')->first();
            if (!empty($myfatoorah)) {
                $myfatoorah->delete();
            }
            $yoco = OnlineGateway::where('keyword', 'yoco')->first();
            if (!empty($yoco)) {
                $yoco->delete();
            }
            $toyyibpay = OnlineGateway::where('keyword', 'toyyibpay')->first();
            if (!empty($toyyibpay)) {
                $toyyibpay->delete();
            }
            $paytabs = OnlineGateway::where('keyword', 'paytabs')->first();
            if (!empty($paytabs)) {
                $paytabs->delete();
            }
            $iyzico = OnlineGateway::where('keyword', 'iyzico')->first();
            if (!empty($iyzico)) {
                $iyzico->delete();
            }
            $midtrans = OnlineGateway::where('keyword', 'midtrans')->first();
            if (!empty($midtrans)) {
                $midtrans->delete();
            }
        });
    }
};

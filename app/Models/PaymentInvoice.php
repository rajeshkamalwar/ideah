<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Published from baselrabia/myfatoorah-with-laravel for MyFatoorahController.
 */
class PaymentInvoice extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 'client_id', 'InvoiceId', 'InvoiceStatus', 'InvoiceValue', 'Currency',
        'InvoiceDisplayValue', 'TransactionId', 'TransactionStatus', 'PaymentGateway',
        'PaymentId', 'CardNumber',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class Payment extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'userid',
        'method',
        'transaction_code',
        'status',
        'total_amount',
        'transaction_uuid',
        // 'product_code',
        // 'signed_field_names',
        // 'signature',
    ];


    public static function savePaymentEsewa($post)
    {
        Payment::create([
            'id'         => (string) Str::uuid(),
            'method'              => 'Esewa',
            'userid'    => $post['userid'],
            'transaction_code'    => $post['transaction_code'],
            'status'              => $post['status'],
            'total_amount'        => $post['total_amount'],
            'transaction_uuid'    => $post['transaction_uuid'],
            // 'product_code'        => $post['product_code'],
            // 'signed_field_names'  => $post['signed_field_names'],
            // 'signature'           => $post['signature'],
        ]);

        return true;
    }
}

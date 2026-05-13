<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Transaction extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function saveTransaction($post)
    {
        try {
            $post['method'] = 'ESEWA';
            $txncode = 'TXN' . strtoupper(bin2hex(random_bytes(5)));
            $insertTransaction = [
                'id'         => (string) Str::uuid(),
                'userid'     => $post['userid'],
                'orgid'     => $post['orgid'],
                'method'     => $post['method'],
                'txncode'     => $txncode,
                'created_at' => Carbon::now(),
            ];

            if (!Transaction::insert($insertTransaction)) {
                throw new \Exception("Couldn't save transaction.");
            }
            return $txncode;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

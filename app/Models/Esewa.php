<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Esewa extends Model
{
    use HasFactory;

    protected $table = 'esewas';

    protected $fillable = [
        'transaction_uuid',
        'booking_id',
        'correlation_id',
        'deeplink',
        'amount',
        'status',
        'product_code',
        'reference_code',
        'esewa_transaction_id',
        'callback_url',
        'redirect_url',
        'properties',
        'esewa_response',
        'paid_at',
    ];

    protected $casts = [
        'amount'          => 'float',
        'properties'      => 'array',
        'esewa_response'  => 'array',
        'paid_at'         => 'datetime',
    ];

    // ---------------------------------------------------------------
    // Status Constants
    // ---------------------------------------------------------------

    const STATUS_BOOKED   = 'BOOKED';
    const STATUS_SUCCESS  = 'SUCCESS';
    const STATUS_PENDING  = 'PENDING';
    const STATUS_FAILED   = 'FAILED';
    const STATUS_CANCELED = 'CANCELED';
    const STATUS_REVERTED = 'REVERTED';

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeBooked($query)
    {
        return $query->where('status', self::STATUS_BOOKED);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_BOOKED, self::STATUS_PENDING]);
    }

    public function markPaid(array $data = []): bool
    {
        return $this->update([
            'status'               => self::STATUS_SUCCESS,
            'reference_code'       => $data['reference_code']  ?? $this->reference_code,
            'esewa_transaction_id' => $data['transaction_id']  ?? $this->esewa_transaction_id,
            'esewa_response'       => $data,
            'paid_at'              => now(),
        ]);
    }
}

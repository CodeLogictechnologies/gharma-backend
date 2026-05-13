<?php

namespace App\Models\API;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'receiverid', 'senderid', 'sender_type', 'message', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class, 'receiverid');
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'senderid');
    }
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }
}

<?php
// app/Events/MessageSent.php
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MessageSent implements ShouldBroadcastNow
{
    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.' . $this->message->user_id)];
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->message->id,
            'message'        => $this->message->message,
            'receiverid' => $this->message->receiverid,
            'sender
            '   => $this->message->sender,
            'created_at'  => $this->message->created_at->toISOString(),
        ];
    }
}

<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
        // Charger les relations nécessaires pour le frontend
        $this->message->load('sender');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Diffuse le message sur un canal privé spécifique à la conversation.
        // Le nom du canal doit être cohérent avec ce que le frontend écoutera.
        // Par exemple, 'private-chat.conversation.{conversation_id}'
        return new PrivateChannel('chat.conversation.' . $this->message->conversation_id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'message.sent'; // Nom de l'événement côté frontend
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        // Utilise la ressource API pour formater les données du message
        // Assurez-vous que MessageResource est correctement défini
        return (new \App\Http\Resources\MessageResource($this->message))->jsonSerialize();
    }
}
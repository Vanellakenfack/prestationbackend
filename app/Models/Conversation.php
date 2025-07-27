<?php

// app/Models/Conversation.php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user1_id', 'user2_id'];

    public function messages()
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function participant1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function participant2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    public function otherParticipant()
    {
        return auth()->id() === $this->user1_id 
            ? $this->participant2 
            : $this->participant1;
    }

    public function markAsRead()
    {
        $this->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', auth()->id())
            ->update(['read_at' => now()]);
    }
}
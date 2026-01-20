<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomTelegramThread extends Model
{
    protected $fillable = [
        'room_id',
        'telegram_chat_id',
        'telegram_channel_message_id',
        'telegram_group_id',
        'telegram_discussion_message_id',
    ];
}

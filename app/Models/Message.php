<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id','type','is_campaign','room_id','sender_id',
        'participant_id','participant_type','organization_id',
        'text','status','external_id','local_id','reply','created_at'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function sender()
    {
        return $this->belongsTo(Sender::class);
    }

    public function reply()
    {
        return $this->belongsTo(Message::class, 'reply', 'id');
    }
}


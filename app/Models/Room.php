<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id','name','description','status','type',
        'channel','channel_account','organization_id',
        'account_uniq_id','channel_integration_id',
        'session','session_at','unread_count',
        'avatar','resolved_at','resolved_by_id',
        'resolved_by_type','external_id','created_at','updated_at'
    ];

    protected $casts = [
        'session_at' => 'datetime',
        'resolved_at' => 'datetime',
        'tags' => 'array',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}

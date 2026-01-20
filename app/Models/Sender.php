<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sender extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id','type','name','avatar_id'];

    public function avatar()
    {
        return $this->belongsTo(Avatar::class);
    }
}
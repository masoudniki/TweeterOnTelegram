<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    //
    protected $table='blocks';
    protected $fillable=[
        'user_id',
        'blocked_id'
    ];

    public function blockedBy(){

        return $this->belongsTo(User::class,'user_id','chat_id');

    }







}

<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    protected $primaryKey='chat_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','username',"firstName","lastName","chat_id",'bio','reply_message_id','label','lastQueryId','lastProfileMessageId'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function tweets(){

        return $this->hasMany(Tweet::class,"user_id","chat_id");

    }
    public function likes(){
        return $this->belongsToMany(Tweet::class,'likes','user_chat_id','tweet_id')->withPivot('action');
    }

    public function blocks(){
        return $this->hasMany(Block::class,'user_id','chat_id');
    }


}

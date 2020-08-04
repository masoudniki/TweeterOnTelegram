<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    //
    protected $fillable=[
        "user_id",
        "body",
        "confirmed",
        "confirmedTime",
        "message_id"


    ];



    public function user(){
        return $this->belongsTo(User::class,"user_id");
    }


    public function likes(){

        return $this->belongsToMany(User::class,'likes','user_chat_id','tweet_id')->withPivot('action');


    }





}

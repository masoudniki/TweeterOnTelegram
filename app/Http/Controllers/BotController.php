<?php

namespace App\Http\Controllers;

use App\library\Telegram;
use App\Tweet;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BotController extends Controller
{
    //

    public $tg;


    public function Main(Request $request){

        $tg=new Telegram("1045187778:AAHZSQBar0XneTe5z-eOpmX3qtQIWG-1vYA");
        $this->tg=$tg;
        if($tg->getUpdateType()=='message') {
            $user = User::find($tg->ChatID());
            if ($user) {
                $step=$user->step;
                switch ($step) {

                    case "home":
                    {
                        if (substr($tg->Text(), 0, 6) == "/start") {
                            $command = explode('-', $tg->Text());
                            if ($command[0] == "/start sendReply") {
                                 $this->showReply($tg);
                            }
                            else{
                                $this->HomeMenu($tg,"home menu");
                            }
                        } else {

                            switch ($tg->Text()) {

                                case "✍🏻 نوشتن توییت و ارسال به کانال" :
                                    $this->showTweetForm($tg);
                                    break;

                                case "🎫 پروفایل من":
                                    $this->ShowUserProfile($tg);
                                    break;

                                case "👤نمایش پروفایل ها":
                                    $tg->sendMessage(['chat_id'=>$tg->ChatID(),'text'=>"ناموسا خسته ام "]);
                                    break;


                                default :
                                    $this->HomeMenu($tg, "دستور شما یافت نشد " . "\n" . "منوی اصلی ");


                            }
                            break;


                        }
                    }
                    break;



                    case "writeTweet" :
                        {

                        switch ($tg->Text()) {

                            case "❎ بیخیال" :
                                $this->HomeMenu($tg, "Home Menu");
                                break;

                            default :
                                $this->StoreTweet($tg);

                        }
                        }
                        break;

                    case 'sendReply' :
                    {
                        switch ($tg->Text()) {
                            case "❎ بیخیال" :
                                $this->HomeMenu($tg, "Home Menu");
                                break;

                            default :
                                $this->sendReply($tg);


                        }
                    }
                    break;
                    case 'sendMsg' :
                    {
                        switch ($tg->Text()) {
                            case "❎ بیخیال" :
                                $this->HomeMenu($tg, "درخواست شما لغو شد ");
                                break;

                            default :
                                $this->sendMessage($tg);


                        }
                    }
                    break;
                    case 'changeLabel' :
                    {
                        switch ($tg->Text()) {

                            default : $this->SaveChanges($tg,'label');

                        }
                    }
                    break;

                    case 'changeBio' :
                    {
                        switch ($tg->Text()) {

                            default : $this->SaveChanges($tg,'bio');

                        }
                    }
                    break;

                    default :
                        return $this->HomeMenu($tg);


                }



            }
            else{
                return $this->signUp($tg);
            }
        }

            elseif($tg->getUpdateType()==="callback_query")
            {

                if(Str::startsWith($tg->Text(),"like")){

                    return $this->likeTweet($tg);

                }
                if(Str::startsWith($tg->Text(),"dislike"))
                {
                    return $this->dislikeTweet($tg);

                }
//                if(STR::startsWith($tg->Text(),"/start sendReply"))
//                {
//                    return $this->showReply($tg);
//                }
                if(Str::startsWith($tg->Text(),"getProfile"))
                {
                    return $this->sendProfile($tg);

                }
                if(Str::startsWith($tg->Text(),'changeBio'))
                {

                    return $this->showChangeForm($tg,'changeBio');

                }if(Str::startsWith($tg->Text(),'changeLabel'))
                {

                    return $this->showChangeForm($tg,'changeLabel');

                }if(Str::startsWith($tg->Text(),'direct'))
                {

                    return $this->sendMessageForm($tg);

                }if(Str::startsWith($tg->Text(),'answerDirect'))
                {

                    return $this->sendMessageForm($tg);

                }if(Str::startsWith($tg->Text(),'blockUser'))
                {

                    return $this->addToBlockList($tg);

                }if(Str::startsWith($tg->Text(),'unblockUser'))
                {

                    return $this->removerFromBlockList($tg);

                }if(Str::startsWith($tg->Text(),'Tweets'))
                {

                    return $this->showTweets($tg);

                }if(Str::startsWith($tg->Text(),'back'))
                {

                    $step=User::findOrFail($tg->UserID())->step;
                    switch ($step)
                    {
                        case 'changeLabel':

                            $this->changeStep($tg,'home');
                            return $this->ShowUserProfile($tg,'editMessage');
                            break;
                        case 'changeBio':

                            $this->changeStep($tg,'home');
                            return $this->ShowUserProfile($tg,'editMessage');
                            break;


                    }

                }


                    $user=User::find($tg->ChatID());

                    if($user && $user->chat_id==env('ADMIN_CHAT_ID'))
                    {
                        return $this->confirmByAdmin($tg);
                    }



            }
        else
        {
            $user=User::find($tg->UserID());
            $step=$user->step;

           // $tg->sendPhoto(['chat_id'=>$tg->ChatID(),'photo'=>$tg->getData()['message']['photo'][0]['file_id']]);








        }
        }




    public function signUp(Telegram $tg){
        $user=new User();
        $user->chat_id=($tg->getUpdateType()==='callback_query') ?$tg->UserID() :$tg->ChatID();
        $user->username=$tg->Username();
        $user->firstName=$tg->FirstName();
        $user->lastName=$tg->LastName();
        $user->save();
        if($tg->getUpdateType()!=='callback_query')
        {
            if(STR::startsWith($tg->Text(),"/start send"))
            {
                $this->showReply($tg);
            }
            else{
                $this->HomeMenu($tg);
            }

        }
        return $user;

    }

    public function HomeMenu(Telegram $tg,$text = null){


        $text=!empty($text) ? $text :"Welcome to The FreeNot Robot";
        $user=User::find($tg->UserID());
        $user->step="home";
        $user->save();
        $keyboard=$tg->buildKeyBoard([
            [
                ['text'=>"✍🏻 نوشتن توییت و ارسال به کانال"]

            ],
            [
                ['text'=>"🎫 پروفایل من"],
            ],
            [
                ["text"=>"👤نمایش پروفایل ها"]
            ]
        ]);
        $tg->sendMessage(["chat_id"=>$tg->ChatID(),"text"=>$text,"reply_markup"=>$keyboard]);




    }
    public function showTweetForm(Telegram $tg){
        if($this->changeStep($tg,"writeTweet")){
            $keyboard=$tg->buildKeyBoard([
                [
                    ['text'=>"❎ بیخیال"]
                ]
            ]);



            $tg->sendMessage([
                "chat_id"=>$tg->ChatID(),
                "text"=>"✏️ توییت مورد نظر خود را به زبان فارسی تایپ کرده و ارسال کنید:",
                "reply_markup"=>$keyboard
            ]);
        }




    }

    public function StoreTweet(Telegram $tg,$reply_tweet=null)
    {
        $User=User::find($tg->ChatID());
        $Tweet=$User->tweets()->create(['body'=>$tg->Text()]);

        return $this->sendForConfirm($tg,$Tweet->id,$reply_tweet);

    }
    public function changeStep(Telegram $tg,$step)
    {
        $User=User::find($tg->ChatID());
        $User->step=$step;
        return $User->save();
    }



    public function sendForConfirm(Telegram $tg,$id,$reply_tweet=null){

        $text="از طرف ==> ".$tg->FirstName()."\n\n";
        $text.="📮توییت ==>".$tg->Text()."\n\n";
        $text.="⌚️زمان ثبت ==>".Carbon::now()->toDateTimeString();

        $id=!empty($reply_tweet)?$id."-".$reply_tweet:$id;
        $keyboard=[
            'inline_keyboard'=>[
                [
                    ['text'=>'Confirm','callback_data'=>
                        $id],['text'=>'Reject','callback_data'=>$id]
                ] ,

            ]
        ];




        $tg->sendMessage(["chat_id"=>env("ADMIN_CHAT_ID"),"text"=>$text,"reply_markup"=>json_encode($keyboard)]);


        $msg="✅پیام شما ثبت و در صف ارسال قرار گرفت!
پس از تایید توسط ادمین ها پست شما در کانال قرار خواهد گرفت.";
       return $this->HomeMenu($tg,$msg);




    }




    public function confirmByAdmin(Telegram $tg){

        $ids=explode('-',$tg->Text());
        $UserTweetId=$ids[0];
        $reply_tweet=count($ids)>1?$ids[1]:null;
        $Tweet=Tweet::find($UserTweetId);
        if(!$Tweet->confirmed) {
            Log::info($Tweet->body);
            $Tweet->confirmed = 1;
            $Tweet->confirmedTime = Carbon::now()->format('Y-m-d H:i:s');
            $Tweet->save();
            return $this->sendToChanel($tg, $Tweet,$reply_tweet);
            }

        return die();


    }

    private function sendToChanel(Telegram $tg,$Tweet,$reply_tweet=null)
    {
        $text=$Tweet->body;
        $text.="\n\n\n";
        $text.="Tweet From ==>".$Tweet->user->firstName;

        $keyboard=
            [
            'inline_keyboard'=>[
                            [
                                ['text'=>'0'.' 👍 ','callback_data'=>'like '.$Tweet->id],['text'=>'0'.' 👎 ','callback_data'=>'dislike '.$Tweet->id]
                            ] ,
                            [
                                ['text'=>"پروفایل کاربر 👤 ","callback_data"=>"getProfile ".$Tweet->user->chat_id]
                            ],
                            [
                                ['text'=>"Reply ✉️ ","url"=>env('ROBOT_LINK')."?start=sendReply-".$Tweet->id]

                            ]


                    ]
            ];
         $content=['chat_id'=>env('CHANNEL_USERNAME'),"text"=>$text,"reply_markup"=>json_encode($keyboard)];

         if($reply_tweet)
         {
             $content['reply_to_message_id']=Tweet::find($reply_tweet)->message_id;
         }


        $result=$tg->sendMessage($content);

        $Tweet->update(['message_id'=>$this->getMsgId($result)]);


        return $this->sendConfirmMessage($tg,$Tweet,$result);



    }

    private function sendConfirmMessage(Telegram $tg,$Tweet,$result)
    {
        $text="✅ توییت شما توسط ادمین ها تایید و ثبت شد.";
        $text.="\n";
        $text.="📮 متن توییت  : ";
        $text.=$Tweet->body;
        $text.="\n\n\n";
        $text.="⌚️زمان ثبت : ";
        $text.=Carbon::now()->toDateTimeString();

        $keyboard=
            [
                'inline_keyboard'=>[
                    [
                        ['text'=>'  👀 مشاهده توییت در کانال','url'=>$this->GenLink($result)]
                    ] ,


                ]
            ];

        ////send confirm message to admin who confirm the tweet
        $tg->answerCallbackQuery(
            [
                'callback_query_id'=>$tg->Callback_Query_ID(),
                'text'=>"با موفقیت تایید شد ",
                "show_alert"=>true
            ]);


        ////send confirm message to user
        $tg->sendMessage(["chat_id"=>$Tweet->user->chat_id,'text'=>$text,'reply_markup'=>json_encode($keyboard)]);



    }

    private function getMsgId($array)
    {

        return $array['result']['message_id'];


    }
    private function GenLink($array){

        $id=$this->getMsgId($array);
        return env('CHANNEL_LINK')."/".$id;


    }




    public function likeTweet(Telegram $tg){

        $user=User::find($tg->UserID());
        Log::info('we');
        if($user)
        {
            $TweetId=explode(' ',$tg->Text())[1];
            $chekLike= $user->likes()->where('tweet_id',$TweetId)->first();

            if(!$chekLike) {
                $user->likes()->attach($TweetId, array('action' => 1));
                $this->updateLikeCount($tg,$TweetId);
            }
            else{
                if(!$chekLike->pivot->action)
                    {
                    $chekLike->pivot->action=1;
                    $chekLike->pivot->save();
                    $this->updateLikeCount($tg,$TweetId);

                }

                $tg->answerCallbackQuery(
                    [
                        'callback_query_id'=>$tg->Callback_Query_ID(),
                        'text'=>"چند بار میخوای لایک کنی داداش",
                        "show_alert"=>true
                    ]);
            }

        }
        else{
            $user=$this->signUp($tg);

        }




    }
    public function dislikeTweet(Telegram $tg){

        $user=User::find($tg->UserID());
        Log::info('we');
        if($user)
        {
            $TweetId=explode(' ',$tg->Text())[1];
            $chekLike= $user->likes()->where('tweet_id',$TweetId)->first();

            if(!$chekLike) {
                $user->likes()->attach($TweetId, array('action' => 0));
                $this->updateLikeCount($tg,$TweetId);
            }

            else{
                if($chekLike->pivot->action)
                    {
                    $chekLike->pivot->action=0;
                    $chekLike->pivot->save();
                    $this->updateLikeCount($tg,$TweetId);

                }
                $tg->answerCallbackQuery(
                    [
                        'callback_query_id'=>$tg->Callback_Query_ID(),
                        'text'=>"چند بار میخوای دیسلایک کنی داداش",
                        "show_alert"=>true
                    ]);
            }

        }
        else{
            $user=$this->signUp($tg);

        }


    }

    public function updateLikeCount(Telegram $tg,$TweetId){

        $LikesCount=DB::select('select count(id) as count from likes where action=? and tweet_id=?',[1,$TweetId])[0]->count;
        $DislikeCount=DB::select('select count(id) as count from likes where action=? and tweet_id=?',[0,$TweetId])[0]->count;
        $UserId=Tweet::find($TweetId)->user->chat_id;

        $keyboard=
            [
                'inline_keyboard'=>[
                    [
                        ['text'=>$LikesCount.' 👍 ','callback_data'=>'like '.$TweetId],['text'=>$DislikeCount.' 👎 ','callback_data'=>'dislike '.$TweetId]
                    ] ,
                    [
                        ['text'=>"پروفایل کاربر 👤 ","callback_data"=>"getProfile ".$UserId]
                    ],
                    [
                        ['text'=>"Reply ✉️ ","url"=>env('ROBOT_LINK')."?start=sendReply-".$TweetId]
                    ]


                ]
            ];

        $tg->answerCallbackQuery(
            [
                'callback_query_id'=>$tg->Callback_Query_ID(),
                'text'=>" ✅ رای شما ثبت شد  ",
            ]);

        $tg->editMessageReplyMarkup(['chat_id'=>$tg->ChatID(),'message_id'=>$tg->MessageID(),'reply_markup'=>json_encode($keyboard)]);



    }

    public function sendProfile(Telegram $tg){

        $UserRequsted=User::find($tg->UserID());
        $params=explode(' ',$tg->Text());
        $UserTweeted=User::find($params[1]);
        if($UserRequsted->step!=='onlyVote')
        {
            $keyboard=
                [
                    'inline_keyboard'=>[
                        [
                            ['text'=>'دایرکت','callback_data'=>'direct '.$UserTweeted->chat_id],['text'=>'توییت ها','callback_data'=>'Tweets '.$UserTweeted->chat_id]
                        ] ,

                    ]
                ];
            $text="Name => ".$UserTweeted->firstName. $UserTweeted->lastName."\n\n";
            $text.="bio =>".$UserTweeted->bio."\n\n";


            if(count($params)>2 && $params[2]=='updateMsg')
            {
                $tg->editMessageText(['chat_id'=>$tg->ChatID(),'message_id'=>$tg->MessageID(),'reply_markup'=>json_encode($keyboard),'text'=>$text]);

            }
            else{

                $tg->sendMessage(['text'=>$text,'reply_markup'=>json_encode($keyboard),'chat_id'=>$UserRequsted->chat_id]);
                $tg->answerCallbackQuery(
                    [
                        'callback_query_id'=>$tg->Callback_Query_ID(),
                        'text'=>"پروفایل در ربات برای شما ارسال شد",
                    ]);

            }




        }
        else{
            $tg->answerCallbackQuery(
                [
                    'callback_query_id'=>$tg->Callback_Query_ID(),
                    'text'=>"باید در ربات وارد شوید",
                ]);
        }
        die();

    }

    private function showReply(Telegram $tg)
    {

        $tweet=Tweet::find(explode('-',$tg->Text())[1]);
        if($tweet)
        {
            $user=User::find($tg->UserID());
            $user->update(['reply_message_id'=>$tweet->id]);
            $text="🗣شما درحال ارسال پاسخ به توییت زیر میباشید."."\n";
            $text.="🗣📃توییت :".$tweet->body."\n\n";
            $text.="📮درصورت تمایل میتوانید پاسخ خودرا که شامل متن ، گیف ، ویدیو ، ویس و... ارسال نمایید.";
            $keyboard=$tg->buildKeyBoard([
                [
                    ['text'=>"❎ بیخیال"]
                ]
            ]);
            $tg->sendMessage(['text'=>$text,'chat_id'=>$tg->UserID(),'reply_markup'=>$keyboard]);
            return $this->changeStep($tg,'sendReply');
        }

    }

    private function sendReply(Telegram $tg,$Type="text")
    {

        if($Type=='text') {
            $user = User::find($tg->ChatID());
            return $this->StoreTweet($tg, $user->reply_message_id);
        }
        if($Type=="photo")
        {

        }
    }

    private function ShowUserProfile(Telegram $tg,$action='sendMessage',$message_id=null)
    {
        $user=User::find($tg->ChatID());
        $user->update(['lastProfileMessageId'=>$tg->MessageID()]);
        $text="نام ==>  "."<a href='t.me/$user->username'>".$user->firstName." ".$user->lastName."</a>"."\n\n";
        $text.="بیو ==>".'<i>'.$user->bio.'</i>'."\n";
        $text.="لقب ==> ".'<i>'.$user->label.'</i>'."\n\n";
        $text.="تعداد پست های ارسالی ==> ".$user->tweets()->count();


        $keyboard=
            [
                'inline_keyboard'=>[
                    [
                        ['text'=>'تغییر لقب','callback_data'=>'changeLabel '],['text'=>'تغییر بیو','callback_data'=>'changeBio ']
                    ] ,
                    [
                        ['text'=>'از لقبم برای اسم توییت استفاده کن','callback_data'=>'SetLabelAsName ']
                    ] ,


                ]
            ];

        $content=['chat_id'=>$tg->ChatID(),'text'=>$text,'parse_mode'=>"HTML",'reply_markup'=>json_encode($keyboard)];

        if($action=='sendMessage')
        {
            $tg->sendMessage($content);
        }elseif ($action=='editMessage'){

            $content['message_id']=empty($message_id) ? $tg->MessageID() : $message_id;
            $tg->editMessageText($content);

        }




    }

    private function showChangeForm(Telegram $tg,$type)
    {
        $user=User::find($tg->ChatID());
        $user->update(['lastProfileMessageId'=>$tg->MessageID()]);
        if($type=='changeLabel')
        {
            $text="لقب فعلی ==> ".(empty($user->label)?'لقبی ندارید':$user->label);
            $text.="\n"."لطفا یک نام ارسال کنید ....";

        }
        elseif ($type=='changeBio')
        {
            $text="بیو فعلی  ==> ".(empty($user->bio)?'بیویی ندارید ':$user->bio);
            $text.="\n\n"."لطفا یک بیو ارسال کنید ....";


        }



        $keyboard=
            [
                'inline_keyboard'=>[
                    [
                        ['text'=>'بازگشت','callback_data'=>'back']
                    ] ,



                ]
            ];


        if($this->changeStep($tg,$type)) {
            $this->storeLastQueryId($user);
            $tg->editMessageText(['chat_id' => $tg->ChatID(), 'message_id' => $tg->MessageID(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => json_encode($keyboard)]);
        }



    }

    private function SaveChanges(Telegram $tg,$type)
    {
        $user=User::find($tg->ChatID());

        if($type=='bio')
        {
            $user->bio=$tg->Text();
        }
        elseif ($type=='label')
        {
            $user->label=$tg->Text();
        }
        $user->save();
        $tg->answerCallbackQuery(['callback_query_id'=>$this->lastQueryId($user),'text'=>'عملیات با موفقیت انجام شد ✅']);
        $this->changeStep($tg,'home');
        return $this->ShowUserProfile($tg,'editMessage',$user->lastProfileMessageId);


    }
    public function lastQueryId(User $user){

        return $user->lastQueryId;

    }
    public function storeLastQueryId(User $user)
    {

        $user->lastQueryId=$this->tg->Callback_Query_ID();
        $user->save();

    }

    public function sendMessageForm(Telegram $tg){
        $directId=explode(' ',$tg->Text())[1];
        $UserRequsted=User::find($tg->UserID());
        $UserRequsted->direct_id=$directId;
        $UserRequsted->save();
        $mentionUser=User::find($directId);
        if(!$mentionUser->blocks()->where(['blocked_id'=>$UserRequsted->chat_id])->first()){

            $text='شما در حال ارسال پیام به '.$mentionUser->firstName.' '.$mentionUser->lastName.'هستید ';
            $text.="\n\n";
            $text.='پیام خودرا ارسال کنید';

            $keyboard=$tg->buildKeyBoard([
                [
                    ['text'=>"❎ بیخیال"]
                ]
            ]);
            $tg->sendMessage(['chat_id'=>$tg->ChatID(),'text'=>$text,'reply_markup'=>$keyboard]);
            $this->changeStep($tg,'sendMsg');


        }
        else{

            $tg->answerCallbackQuery(
                [
                    'callback_query_id'=>$tg->Callback_Query_ID(),
                    'text'=>"حاجی بلاکت کرده 😕",
                ]);

        }


    }
    public function sendMessage(Telegram $tg){

        $userMentioned=User::find(User::find($tg->UserID())->direct_id);
        $text='پیام شما با موفقیت به '.$userMentioned->firstName . 'ارسال شد ';
        $this->HomeMenu($tg,$text);
        $this->forWardMSG($tg->UserID(),$userMentioned->chat_id,$tg->Text());

    }

    public function forWardMSG($userId,$userMentionedId,$msg)
    {
        $text='یک پیام جدید از'.User::find($userId)->firstName;
        $text.="\n\n";
        $text.='متن پیام ==> '.$msg;
        $keyboard=
            [
                'inline_keyboard'=>[
                    [
                        ['text'=>'پاسخ','callback_data'=>'answerDirect '.$userId],['text'=>'بلاک','callback_data'=>'blockUser '.$userId]
                    ] ,
                ]
            ];

        $this->tg->sendMessage(['chat_id'=>$userMentionedId,'text'=>$text,'reply_markup'=>json_encode($keyboard)]);

    }

    public function addToBlockList(Telegram $tg)
    {
        $blockId=explode(' ',$tg->Text())[1];
        if($blockId!=$tg->UserID())
        {
            $user=User::find($tg->UserID());
            $user->blocks()->create(['blocked_id'=>$blockId]);

            $keyboard=
                [
                    'inline_keyboard'=>[
                        [
                            ['text'=>'آنبلاک','callback_data'=>'unblockUser '.$blockId]
                        ] ,
                    ]
                ];
            $tg->answerCallbackQuery(
                [
                    'callback_query_id'=>$tg->Callback_Query_ID(),
                    'text'=>" ✅ کاربر با موفقیت بلاک شد",
                ]);

            $tg->editMessageReplyMarkup(['chat_id'=>$tg->ChatID(),'message_id'=>$tg->MessageID(),'reply_markup'=>json_encode($keyboard)]);


        }
        else
            {

                $tg->answerCallbackQuery(
                    [
                        'callback_query_id'=>$tg->Callback_Query_ID(),
                        'text'=>"داش خودتو نمیتونی بلاک کنی",
                    ]);

        }




    }
    public function removerFromBlockList(Telegram $tg){

        $blockId=explode(' ',$tg->Text())[1];


        $user=User::find($tg->UserID());
        $user->blocks()->where(['blocked_id'=>$blockId])->first()->delete();

        $keyboard=
            [
                'inline_keyboard'=>[
                    [
                        ['text'=>'پاسخ','callback_data'=>'answerDirect '.$blockId],['text'=>'بلاک','callback_data'=>'blockUser '.$blockId]
                    ] ,
                ]
            ];
        $tg->answerCallbackQuery(
            [
                'callback_query_id'=>$tg->Callback_Query_ID(),
                'text'=>" ✅ کاربر با موفقیت انبلاک شد",
            ]);

        $tg->editMessageReplyMarkup(['chat_id'=>$tg->ChatID(),'message_id'=>$tg->MessageID(),'reply_markup'=>json_encode($keyboard)]);

    }

    private function showTweets(Telegram $tg,$page=1)
    {
        $params=explode(' ',$tg->Text());
        if(count($params)>2)
        {
            $page=$params[2];
        }
        if(count($params)>2 & $page-1<=0 && $params[3]=='previous'){

            $tg->answerCallbackQuery(
                [
                    'callback_query_id'=>$tg->Callback_Query_ID(),
                    'text'=>"حاجی تموم شد دیگه عقب تر نداریم :|",
                ]);
                exit();
        }
        $UserId=$params[1];
        $user=User::find($UserId);
        $lastPage=round($user->tweets->count()/5);
        if(count($params)>2 && $page+1>$lastPage && $params[3]=='next')
        {
            $tg->answerCallbackQuery(
                [
                    'callback_query_id'=>$tg->Callback_Query_ID(),
                    'text'=>"اخرشه دیگه خوش اومدی  :|",
                ]);
            exit();
        }

        $Tweets=$user->tweets->forPage($page,5);
        $text='Name '.$user->firstName."\n\n";
        $text.="Tweets \n";



        $prevPage=($page-1) > 0         ? $page-1  :      1;
        $nextPage=($page+1) > $lastPage ? $lastPage:$page+1;

        foreach ($Tweets as $tweet)
        {
            $text.="\n".$tweet->body."\n";

        }

        $text.='صفحه' .$page;


        $keyboard=
            [
                'inline_keyboard'=>[
                    [
                        ['text'=>'صفحه قبل','callback_data'=>'Tweets '.$user->chat_id.' '.$prevPage.' '.'previous'],['text'=>'Home Page','callback_data'=>'getProfile '.$user->chat_id.' '.'updateMsg'],['text'=>'صفحه بعد','callback_data'=>'Tweets '.$user->chat_id.' '.$nextPage.' '.'next']
                    ] ,
                ]
            ];

        $tg->editMessageText(['chat_id'=>$tg->ChatID(),'message_id'=>$tg->MessageID(),'reply_markup'=>json_encode($keyboard),'text'=>$text]);



    }


}

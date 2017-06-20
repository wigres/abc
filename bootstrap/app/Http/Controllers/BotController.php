<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \LINE\LINEBot;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;

class BotController extends Controller
{
    /**
     * Main Bot Controller.
     *
     * @return void
     */

    private $bot;
    private $httpClient;

    public function __construct()
    {   
        $this->httpClient = new CurlHTTPClient(env('CHANNEL_ACCESS_TOKEN'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => env('CHANNEL_SECRET')]);
    }

    public function test()
    {        
        $message = "hello from laravel";
        $user_id = "U409339b3512f5a91a9cf45a7214a3955";
        $textMessageBuilder = new TextMessageBuilder($message);
        $push = $this->bot->pushMessage($user_id, $textMessageBuilder);

        $res = array(
            'code' => $push->getHTTPStatus(),
            'body' => $push->getRawBody(),
        );

        return response($res);
    }

    public function hello()
    {
        $res = array(
            'code' => 200,
            'message' => "hello world from bot controller"
        );

        return response($res);
    }

    public function callback(Request $request)
    {
        $signature = $request->server('HTTP_X_LINE_SIGNATURE');
        $body = $request->getContent();

        // is LINE_SIGNATURE exists in request header?
        if (empty($signature)){
            $res = array('code' => 400, 'message' => 'Signature not set');
            return response($res);
        }

        // is this request comes from LINE?
        if(env('PASS_SIGNATURE') == false && ! $this->bot->validateSignature($body, $signature)){
            $res = array('code' => 400, 'message' => 'Invalid signature');
            return response($res);
        }

        $data = json_decode($body, true);
        foreach ($data['events'] as $event){
            if ($event['type'] == 'message'){
                if($event['message']['type'] == 'text'){
                    $message = "Hai... ";
                    $push = $this->bot->replyText($event['replyToken'], $message);
                    $res = array(
                        'code' => $push->getHTTPStatus(),
                        'body' => $push->getRawBody(),
                    );

                    return response($res);
                }
            
            }
        }

        $res = array('code' => 200, 'message' => 'No Event');
        return response($res);

    }

}

<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramController extends Controller
{
    public function send($message)
    {
       $chat_id = '6743906881'; // ✅ Your working chat ID
    $bot_token = '7287253308:AAFX63ArvBktT7U1CvfAL58mxT6ryT0vT0Y';

        $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";

        $post_fields = [
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $response = Http::post($url, $post_fields);

        return $response->json();
    }
}

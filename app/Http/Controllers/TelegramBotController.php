<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TelegramBotController extends Controller
{
    public function handle(Request $request)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $data = $request->all();

        $chat_id = $data['message']['chat']['id'] ?? null;
        $text    = trim($data['message']['text'] ?? '');

        if ($text === '/start') {
            Telegram::sendMessage([
                'chat_id' => $chat_id,
                'text' => "Введите логин и пароль через пробел:\n\nlogin password"
            ]);
            return;
        }

        // Логин + пароль
        if (str_contains($text, ' ')) {
            [$login, $password] = explode(' ', $text);

            $user = User::where('email', $login)->first();

            if ($user && Hash::check($password, $user->password)) {

                $user->update([
                    'telegram_chat_id' => $chat_id,
                    'telegram_subscribed' => true
                ]);

                Telegram::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => "Успешная подписка на уведомления!"
                ]);

            } else {
                Telegram::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => "Неверные данные!"
                ]);
            }
        }
    }

}

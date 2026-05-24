<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GoodsReceiptItem;
use App\Models\Notification;
use Carbon\Carbon;

class CheckExpiryDates extends Command
{
    protected $signature = 'stock:check-expiry';
    protected $description = 'Check expiry dates and create notifications';

    public function handle()
    {
        $now = Carbon::now();

        GoodsReceiptItem::with('receipt', 'product')
            ->whereNotNull('expiry_date')
            ->where('quantity', '>', 0)
            ->get()
            ->each(function ($item) use ($now) {

                $expiry = Carbon::parse($item->expiry_date);
                $diff = $now->diffInDays($expiry, false);

                if ($diff <= 5 && $diff >= 0) {

                    // Уже есть такое уведомление?
                    $exists = Notification::where('type', 'expiry')
                        ->where('product_id', $item->product_id)
                        ->where('batch', $item->batch)
                        ->exists();

                    if (!$exists) {
                        $notification = Notification::create([
                            'type' => 'expiry',
                            'message' => "Осталось {$diff} дней до истечения партии {$item->batch} ({$item->product->name})",
                            'product_id' => $item->product_id,
                            'batch' => $item->batch,
                        ]);

// ⬇️ Вставляем отправку в Telegram
                        $users = User::where('telegram_subscribed', true)->get();

                        foreach ($users as $u) {
                            Telegram::sendMessage([
                                'chat_id' => $u->telegram_chat_id,
                                'text' => $notification->message
                            ]);
                        }
                    }
                }
            });

        return Command::SUCCESS;
    }
}


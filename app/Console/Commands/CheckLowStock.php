<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Notification;

class CheckLowStock extends Command
{
    protected $signature = 'stock:check-low';
    protected $description = 'Check minimum stock levels';

    public function handle()
    {
        Product::with('stocks.warehouse')->get()->each(function($product){

            foreach ($product->stocks as $stock) {

                if ($product->min_qty > 0 && $stock->quantity < $product->min_qty) {

                    $exists = Notification::where('type','low_stock')
                        ->where('product_id',$product->id)
                        ->where('warehouse_id',$stock->warehouse_id)
                        ->exists();

                    if (!$exists) {
                        $notification = Notification::create([
                            'type' => 'low_stock',
                            'message' => "Товар {$product->name} заканчивается на складе {$stock->warehouse->name}. Остаток: {$stock->quantity}",
                            'product_id' => $product->id,
                            'warehouse_id' => $stock->warehouse_id,
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

            }

        });

        return Command::SUCCESS;
    }
}

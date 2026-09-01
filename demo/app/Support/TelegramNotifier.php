<?php

namespace App\Support;

use App\Models\Order;
use App\Models\VenueSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pings the venue's staff over a Telegram bot (created with @BotFather) when a
 * new order arrives. Best-effort: it never throws and never blocks placing the
 * order. With no bot token / chat id configured (local, tests, CI) it silently
 * no-ops, so nothing external is called unless the server is deliberately set up.
 */
class TelegramNotifier
{
    public function isConfigured(): bool
    {
        return (bool) config('services.telegram.bot_token')
            && (bool) config('services.telegram.admin_chat_id');
    }

    /** Send a formatted "new order" message to staff. */
    public function notifyNewOrder(Order $order): bool
    {
        return $this->send($this->orderMessage($order));
    }

    private function orderMessage(Order $order): string
    {
        $lines = [];
        $lines[] = '🧾 Новый заказ №'.$order->id;

        if ($order->table_number) {
            $lines[] = 'Стол: '.$order->table_number;
        }

        $lines[] = '';
        foreach ($order->items as $item) {
            $lines[] = '• '.$item->name('ru').' × '.$item->quantity.' — '.$item->formattedLineTotal();
        }

        $lines[] = '';
        $lines[] = 'Итого: '.$order->formattedTotal();

        if ($order->comment) {
            $lines[] = '';
            $lines[] = 'Комментарий: '.$order->comment;
        }

        $lines[] = '';
        $lines[] = VenueSetting::current()->name;

        return implode("\n", $lines);
    }

    private function send(string $message): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.admin_chat_id');

        try {
            $response = Http::timeout(8)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'disable_web_page_preview' => true,
                ]);

            if ($response->failed()) {
                Log::warning('Telegram order notification rejected', [
                    'status' => $response->status(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $e) {
            // A notification must never break placing the order.
            Log::warning('Telegram order notification failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

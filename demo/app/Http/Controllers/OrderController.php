<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Dish;
use App\Models\Order;
use App\Models\VenueSetting;
use App\Support\TelegramNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Accept a guest order from the table. The client sends only dish ids and
     * quantities; we re-resolve each dish from the database — its live price and
     * availability — so the bill can never be tampered with client-side, and a
     * dish that just went onto the stop-list is rejected rather than sold.
     */
    public function store(StoreOrderRequest $request, TelegramNotifier $telegram): JsonResponse
    {
        $venue = VenueSetting::current();

        // Respect the owner's master switch — ordering can be turned off.
        abort_unless($venue->ordering_enabled, 404);

        $data = $request->validated();
        $lines = collect($data['items']);

        // Only visible, in-stock dishes are orderable. Anything else the client
        // knows about is stale (hidden or sold out since the page loaded).
        $dishes = Dish::query()
            ->whereIn('id', $lines->pluck('id'))
            ->where('is_visible', true)
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        $missing = $lines->first(fn ($line) => ! $dishes->has($line['id']));
        if ($missing) {
            throw ValidationException::withMessages([
                'items' => 'Некоторые блюда уже недоступны. Обновите страницу.',
            ]);
        }

        $locale = ($data['locale'] ?? $venue->default_locale) === 'kk' ? 'kk' : 'ru';

        $order = DB::transaction(function () use ($lines, $dishes, $data, $locale) {
            $order = Order::create([
                'table_number' => $data['table_number'] ?? null,
                'comment' => $data['comment'] ?? null,
                'locale' => $locale,
                'status' => Order::STATUS_NEW,
                'total' => 0,
            ]);

            $total = 0;
            foreach ($lines as $line) {
                /** @var Dish $dish */
                $dish = $dishes->get($line['id']);
                $qty = (int) $line['qty'];
                $total += (int) $dish->price * $qty;

                $order->items()->create([
                    'dish_id' => $dish->id,
                    'name_ru' => $dish->name_ru,
                    'name_kk' => $dish->name_kk,
                    'price' => (int) $dish->price,
                    'quantity' => $qty,
                ]);
            }

            $order->update(['total' => $total]);

            return $order;
        });

        // Best-effort — never blocks or fails the order if Telegram is down/unset.
        $telegram->notifyNewOrder($order->load('items'));

        return response()->json([
            'id' => $order->id,
            'number' => $order->id,
            'status' => $order->status,
        ], 201);
    }
}

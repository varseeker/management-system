<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuSale;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExternalOrderService
{
    public function process(array $payload): array
    {
        $externalOrderId = (string) $payload['external_order_id'];

        if (MenuSale::where('external_order_id', $externalOrderId)->exists()) {
            return [
                'status' => 'duplicate',
                'message' => 'Order already processed.',
                'external_order_id' => $externalOrderId,
            ];
        }

        $items = $payload['items'] ?? [];

        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Order must contain at least one item.',
            ]);
        }

        $user = User::where('email', config('inventory.pos_user_email'))->first();

        if (! $user) {
            abort(503, 'POS integration user is not configured.');
        }

        $processedItems = [];
        $posOrderId = $this->resolvePosOrderId($externalOrderId, $payload);

        DB::transaction(function () use ($payload, $externalOrderId, $items, $user, $posOrderId, &$processedItems) {
            $posOrder = PosOrder::create([
                'external_order_id' => $externalOrderId,
                'pos_order_id' => $posOrderId,
                'customer' => $payload['customer'] ?? null,
                'total' => (int) ($payload['order_total'] ?? 0),
                'amount_paid' => (int) ($payload['amount_paid'] ?? 0),
                'amount_change' => (int) ($payload['amount_change'] ?? 0),
                'order_status' => $payload['order_status'] ?? 'paid',
                'payment_status' => $payload['payment_status'] ?? 'success',
                'payment_method' => $payload['payment_method'] ?? null,
                'payment_reference' => $payload['payment_reference'] ?? null,
                'cashier_name' => $payload['cashier_name'] ?? null,
                'source' => $payload['source'] ?? 'pos-warkop-kayu',
                'ordered_at' => $payload['ordered_at'] ?? now(),
            ]);

            foreach ($items as $row) {
                $menuCode = (string) ($row['menu_code'] ?? '');
                $quantity = (int) ($row['quantity'] ?? 0);

                if ($menuCode === '' || $quantity < 1) {
                    throw ValidationException::withMessages([
                        'items' => 'Each item requires menu_code and quantity.',
                    ]);
                }

                $menu = Menu::with('rawMaterials')
                    ->where('code', $menuCode)
                    ->where('is_active', true)
                    ->first();

                if (! $menu) {
                    throw ValidationException::withMessages([
                        'items' => "Menu {$menuCode} not found or inactive.",
                    ]);
                }

                if ($menu->rawMaterials->isEmpty()) {
                    throw ValidationException::withMessages([
                        'items' => "Menu {$menuCode} has no recipe configured.",
                    ]);
                }

                if (! $menu->hasEnoughStock($quantity)) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient raw material stock for {$menu->name} x{$quantity}.",
                    ]);
                }

                $menu->consumeStock($quantity);

                MenuSale::create([
                    'menu_id' => $menu->id,
                    'user_id' => $user->id,
                    'quantity' => $quantity,
                    'note' => $this->buildNote($payload, $row),
                    'external_order_id' => $externalOrderId,
                    'source' => $payload['source'] ?? 'pos-warkop-kayu',
                    'payment_method' => $payload['payment_method'] ?? null,
                    'customer' => $payload['customer'] ?? null,
                ]);

                PosOrderItem::create([
                    'pos_order_id' => $posOrder->id,
                    'menu_code' => $menuCode,
                    'menu_name' => (string) ($row['menu_name'] ?? $menu->name),
                    'menu_price' => (int) ($row['menu_price'] ?? $menu->price),
                    'quantity' => $quantity,
                    'variant' => $row['variant'] ?? null,
                    'size' => $row['size'] ?? null,
                    'ice' => $row['ice'] ?? null,
                    'sugar' => $row['sugar'] ?? null,
                    'subtotal' => (int) ($row['subtotal'] ?? ($menu->price * $quantity)),
                    'status' => $row['status'] ?? 'Done',
                ]);

                $processedItems[] = [
                    'menu_code' => $menu->code,
                    'menu_name' => $menu->name,
                    'quantity' => $quantity,
                ];
            }

            if ($posOrder->total <= 0) {
                $posOrder->update([
                    'total' => $posOrder->items()->sum('subtotal'),
                    'amount_paid' => $posOrder->amount_paid > 0
                        ? $posOrder->amount_paid
                        : $posOrder->items()->sum('subtotal'),
                ]);
            }
        });

        return [
            'status' => 'success',
            'message' => 'Order processed and stock updated.',
            'external_order_id' => $externalOrderId,
            'items' => $processedItems,
        ];
    }

    private function resolvePosOrderId(string $externalOrderId, array $payload): ?int
    {
        if (! empty($payload['pos_order_id'])) {
            return (int) $payload['pos_order_id'];
        }

        if (preg_match('/(\d+)$/', $externalOrderId, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function buildNote(array $payload, array $row): ?string
    {
        $parts = array_filter([
            isset($row['variant']) && $row['variant'] !== '-' ? 'Variant: '.$row['variant'] : null,
            isset($row['size']) && $row['size'] !== '-' ? 'Size: '.$row['size'] : null,
            isset($row['ice']) && $row['ice'] !== '-' ? 'Ice: '.$row['ice'] : null,
            isset($row['sugar']) && $row['sugar'] !== '-' ? 'Sugar: '.$row['sugar'] : null,
            isset($payload['payment_method']) ? 'Payment: '.$payload['payment_method'] : null,
        ]);

        return $parts === [] ? null : implode(' | ', $parts);
    }
}

<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuSale;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ExternalOrderService
{
    public function process(array $payload): array
    {
        $externalOrderId = (string) $payload['external_order_id'];

        if (PosOrder::where('external_order_id', $externalOrderId)->exists()) {
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

        $user = $this->resolveIntegrationUser();
        $processedItems = [];
        $warnings = [];
        $posOrderId = $this->resolvePosOrderId($externalOrderId, $payload);

        DB::transaction(function () use ($payload, $externalOrderId, $items, $user, $posOrderId, &$processedItems, &$warnings) {
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
                    ->first();

                PosOrderItem::create([
                    'pos_order_id' => $posOrder->id,
                    'menu_code' => $menuCode,
                    'menu_name' => (string) ($row['menu_name'] ?? $menu?->name ?? $menuCode),
                    'menu_price' => (int) ($row['menu_price'] ?? $menu?->price ?? 0),
                    'quantity' => $quantity,
                    'variant' => $row['variant'] ?? null,
                    'size' => $row['size'] ?? null,
                    'ice' => $row['ice'] ?? null,
                    'sugar' => $row['sugar'] ?? null,
                    'subtotal' => (int) ($row['subtotal'] ?? (($menu?->price ?? 0) * $quantity)),
                    'status' => $row['status'] ?? 'Done',
                ]);

                $stockResult = $this->tryConsumeStock($menu, $quantity, $menuCode);

                if ($stockResult === true) {
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

                    $processedItems[] = [
                        'menu_code' => $menu->code,
                        'menu_name' => $menu->name,
                        'quantity' => $quantity,
                        'stock_updated' => true,
                    ];

                    continue;
                }

                $warnings[] = $stockResult;
                $processedItems[] = [
                    'menu_code' => $menuCode,
                    'menu_name' => (string) ($row['menu_name'] ?? $menu?->name ?? $menuCode),
                    'quantity' => $quantity,
                    'stock_updated' => false,
                    'warning' => $stockResult,
                ];
            }

            if ($posOrder->total <= 0) {
                $lineTotal = $posOrder->items()->sum('subtotal');

                $posOrder->update([
                    'total' => $lineTotal,
                    'amount_paid' => $posOrder->amount_paid > 0 ? $posOrder->amount_paid : $lineTotal,
                ]);
            }
        });

        if ($warnings !== []) {
            Log::warning('POS order recorded with stock warnings.', [
                'external_order_id' => $externalOrderId,
                'warnings' => $warnings,
            ]);
        }

        return [
            'status' => 'success',
            'message' => $warnings === []
                ? 'Order processed and stock updated.'
                : 'Order recorded. Some items could not update stock.',
            'external_order_id' => $externalOrderId,
            'items' => $processedItems,
            'warnings' => $warnings,
        ];
    }

    private function tryConsumeStock(?Menu $menu, int $quantity, string $menuCode): bool|string
    {
        if (! $menu) {
            return "Menu {$menuCode} tidak ditemukan di inventory — pesanan tetap dicatat.";
        }

        if (! $menu->is_active) {
            return "Menu {$menu->name} tidak aktif — stok tidak dikurangi.";
        }

        if ($menu->rawMaterials->isEmpty()) {
            return "Menu {$menu->name} belum punya resep bahan baku — stok tidak dikurangi.";
        }

        if (! $menu->hasEnoughStock($quantity)) {
            return "Stok bahan baku {$menu->name} tidak cukup untuk {$quantity} porsi — pesanan tetap dicatat.";
        }

        $menu->consumeStock($quantity);

        return true;
    }

    private function resolveIntegrationUser(): User
    {
        $email = config('inventory.pos_user_email', 'pos-integration@system.local');

        $user = User::where('email', $email)->first();

        if ($user) {
            return $user;
        }

        Log::warning('POS integration user missing — creating fallback user.', ['email' => $email]);

        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'POS Warkop Kayu',
                'role' => 'staff',
                'password' => bcrypt(str()->random(32)),
            ],
        );
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

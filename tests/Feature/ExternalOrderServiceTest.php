<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\PosOrder;
use App\Models\RawMaterial;
use App\Models\User;
use App\Services\ExternalOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_order_is_recorded_even_when_stock_is_insufficient(): void
    {
        User::factory()->create([
            'email' => 'pos-integration@system.local',
            'role' => 'staff',
        ]);

        $material = RawMaterial::query()->create([
            'code' => 'TELUR-01',
            'name' => 'Telur',
            'stock' => 0,
            'unit' => 'butir',
        ]);

        $menu = Menu::query()->create([
            'code' => 'IND-TELUR',
            'name' => 'Indomie Telur',
            'price' => 18000,
            'category' => 'Snack',
            'most_ordered' => false,
            'is_active' => true,
        ]);

        $menu->rawMaterials()->sync([
            $material->id => ['quantity' => 1],
        ]);

        $service = app(ExternalOrderService::class);

        $result = $service->process([
            'external_order_id' => 'pos-warkop-kayu-99',
            'pos_order_id' => 99,
            'customer' => 'Budi',
            'payment_method' => 'Cash',
            'order_total' => 18000,
            'amount_paid' => 20000,
            'amount_change' => 2000,
            'order_status' => 'paid',
            'payment_status' => 'success',
            'items' => [
                [
                    'menu_code' => 'IND-TELUR',
                    'menu_name' => 'Indomie Telur',
                    'menu_price' => 18000,
                    'quantity' => 1,
                    'subtotal' => 18000,
                ],
            ],
        ]);

        $this->assertSame('success', $result['status']);
        $this->assertNotEmpty($result['warnings']);
        $this->assertDatabaseHas('pos_orders', [
            'external_order_id' => 'pos-warkop-kayu-99',
            'customer' => 'Budi',
        ]);
        $this->assertDatabaseHas('pos_order_items', [
            'menu_code' => 'IND-TELUR',
            'menu_name' => 'Indomie Telur',
        ]);
    }

    public function test_duplicate_external_order_id_is_ignored(): void
    {
        User::factory()->create([
            'email' => 'pos-integration@system.local',
            'role' => 'staff',
        ]);

        PosOrder::query()->create([
            'external_order_id' => 'pos-warkop-kayu-1',
            'total' => 10000,
            'amount_paid' => 10000,
            'ordered_at' => now(),
        ]);

        $result = app(ExternalOrderService::class)->process([
            'external_order_id' => 'pos-warkop-kayu-1',
            'items' => [
                ['menu_code' => 'X', 'menu_name' => 'Test', 'quantity' => 1],
            ],
        ]);

        $this->assertSame('duplicate', $result['status']);
    }
}

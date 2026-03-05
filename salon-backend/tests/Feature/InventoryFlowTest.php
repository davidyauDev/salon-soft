<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Service;
use App\Models\Stylist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventoryFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_and_sale_flow(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user);

        $item = Item::query()->create([
            'name' => 'Shampoo',
            'type' => 'mixed',
            'base_unit' => 'ml',
            'sale_price' => 20,
            'stock_minimum' => 100,
            'is_active' => true,
        ]);

        $item->units()->createMany([
            ['unit' => 'ml', 'factor_to_base' => 1, 'is_base' => true],
            ['unit' => 'l', 'factor_to_base' => 1000, 'is_base' => false],
        ]);

        $purchasePayload = [
            'item_id' => $item->id,
            'quantity' => 1,
            'unit' => 'l',
            'cost_per_unit' => 50,
        ];

        $this->postJson('/api/purchases', $purchasePayload)
            ->assertStatus(201);

        $this->assertDatabaseHas('stock_lots', [
            'item_id' => $item->id,
        ]);

        $salePayload = [
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 200,
                    'unit' => 'ml',
                    'unit_price' => 0.05,
                ],
            ],
            'payment_method' => 'cash',
        ];

        $this->postJson('/api/sales', $salePayload)
            ->assertStatus(201);

        $this->assertDatabaseHas('inventory_moves', [
            'item_id' => $item->id,
            'type' => 'sale',
        ]);
    }

    public function test_service_consumption_creates_commission(): void
    {
        $user = User::factory()->create(['role' => 'stylist']);
        Sanctum::actingAs($user);
        $stylist = Stylist::query()->create([
            'user_id' => $user->id,
            'commission_rate' => 20,
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'name' => 'Color',
            'base_price' => 120,
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'name' => 'Oxidant',
            'type' => 'consume_only',
            'base_unit' => 'ml',
            'sale_price' => null,
            'stock_minimum' => 0,
            'is_active' => true,
        ]);

        $item->units()->create([
            'unit' => 'ml',
            'factor_to_base' => 1,
            'is_base' => true,
        ]);

        $this->postJson('/api/purchases', [
            'item_id' => $item->id,
            'quantity' => 500,
            'unit' => 'ml',
            'cost_per_unit' => 0.02,
        ])->assertStatus(201);

        $this->postJson('/api/service-records', [
            'service_id' => $service->id,
            'stylist_id' => $stylist->id,
            'total_amount' => 120,
            'payment_method' => 'card',
            'consumptions' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 50,
                    'unit' => 'ml',
                ],
            ],
        ])->assertStatus(201);

        $this->assertDatabaseHas('commissions', [
            'stylist_id' => $stylist->id,
            'amount' => 24.00,
        ]);
    }
}

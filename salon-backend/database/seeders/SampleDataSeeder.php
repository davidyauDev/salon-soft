<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Item;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use App\Models\Stylist;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\SaleService;
use App\Services\ServiceRecordService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@salon.test')->first();

        if (!$admin) {
            $admin = User::query()->create([
                'name' => 'Administrador',
                'email' => 'admin@salon.test',
                'password' => Hash::make('secret123'),
                'role' => 'admin',
            ]);
        }

        $stylists = collect([
            ['name' => 'Marta Rios', 'email' => 'marta@salon.test', 'rate' => 20],
            ['name' => 'Daniel Ramos', 'email' => 'daniel@salon.test', 'rate' => 18],
            ['name' => 'Jazmin Paredes', 'email' => 'jazmin@salon.test', 'rate' => 22],
            ['name' => 'Valeria Cruz', 'email' => 'valeria@salon.test', 'rate' => 19],
            ['name' => 'Paula Vega', 'email' => 'paula@salon.test', 'rate' => 21],
        ])->map(function (array $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('secret123'),
                    'role' => 'stylist',
                ],
            );

            return Stylist::query()->updateOrCreate(
                ['user_id' => $user->id],
                ['commission_rate' => $data['rate'], 'is_active' => true],
            );
        });

        if (Client::query()->count() < 20) {
            for ($i = 0; $i < 35; $i++) {
                $firstName = fake()->firstName();
                $lastName = fake()->lastName();

                Client::query()->create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'full_name' => trim($firstName . ' ' . $lastName),
                    'phone' => fake()->optional()->numerify('9########'),
                    'email' => fake()->optional()->safeEmail(),
                    'dni' => fake()->optional()->numerify('########'),
                    'address' => fake()->optional()->address(),
                    'birth_date' => fake()->optional()->date('Y-m-d'),
                    'gender' => fake()->optional()->randomElement(['female', 'male', 'other', 'unspecified']),
                    'notes' => fake()->optional()->sentence(),
                ]);
            }
        }

        $clients = Client::query()->get();

        $categories = collect([
            ['name' => 'Cabello'],
            ['name' => 'Coloracion'],
            ['name' => 'Tratamientos'],
            ['name' => 'Styling'],
            ['name' => 'Higiene'],
            ['name' => 'Accesorios'],
        ])->map(function (array $data) {
            return Category::query()->updateOrCreate(
                ['name' => $data['name']],
                ['description' => null, 'is_active' => true],
            );
        });

        $brands = collect([
            ['name' => 'Wella'],
            ['name' => 'Loreal'],
            ['name' => 'Kerastase'],
            ['name' => 'Schwarzkopf'],
            ['name' => 'Generic'],
        ])->map(function (array $data) {
            return Brand::query()->updateOrCreate(
                ['name' => $data['name']],
                ['is_active' => true],
            );
        });

        $suppliers = collect([
            ['name' => 'Distribuidora Andina', 'contact_name' => 'Rosa Gomez', 'phone' => '999111222'],
            ['name' => 'Salon Supplies SAC', 'contact_name' => 'Luis Perez', 'phone' => '988444555'],
            ['name' => 'Beauty Import', 'contact_name' => 'Carla Diaz', 'phone' => '977888333'],
        ])->map(function (array $data) {
            return Supplier::query()->updateOrCreate(
                ['name' => $data['name']],
                [
                    'contact_name' => $data['contact_name'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'email' => null,
                    'address' => null,
                    'notes' => null,
                    'is_active' => true,
                ],
            );
        });

        $categoryMap = $categories->keyBy('name');
        $brandMap = $brands->keyBy('name');

        $itemDefinitions = [
            [
                'name' => 'Shampoo Premium',
                'type' => 'mixed',
                'base_unit' => 'ml',
                'sale_price' => 0.12,
                'stock_minimum' => 800,
                'category' => 'Cabello',
                'brand' => 'Loreal',
                'sku' => 'SHAM-300',
            ],
            [
                'name' => 'Tinte Avellana',
                'type' => 'consume_only',
                'base_unit' => 'ml',
                'sale_price' => null,
                'stock_minimum' => 500,
                'category' => 'Coloracion',
                'brand' => 'Wella',
                'sku' => 'TIN-AV-01',
            ],
            [
                'name' => 'Oxidante 20v',
                'type' => 'consume_only',
                'base_unit' => 'ml',
                'sale_price' => null,
                'stock_minimum' => 400,
                'category' => 'Coloracion',
                'brand' => 'Wella',
                'sku' => 'OXI-20V',
            ],
            [
                'name' => 'Mascarilla Repara',
                'type' => 'mixed',
                'base_unit' => 'ml',
                'sale_price' => 0.18,
                'stock_minimum' => 600,
                'category' => 'Tratamientos',
                'brand' => 'Kerastase',
                'sku' => 'MASK-RE',
            ],
            [
                'name' => 'Crema Peinar',
                'type' => 'mixed',
                'base_unit' => 'ml',
                'sale_price' => 0.15,
                'stock_minimum' => 500,
                'category' => 'Styling',
                'brand' => 'Schwarzkopf',
                'sku' => 'CRE-PEI',
            ],
            [
                'name' => 'Guantes Nitrilo',
                'type' => 'mixed',
                'base_unit' => 'und',
                'sale_price' => 1.2,
                'stock_minimum' => 50,
                'category' => 'Higiene',
                'brand' => 'Generic',
                'sku' => 'GUA-NIT',
            ],
            [
                'name' => 'Ampolla Capilar',
                'type' => 'sell_only',
                'base_unit' => 'und',
                'sale_price' => 8.5,
                'stock_minimum' => 12,
                'category' => 'Tratamientos',
                'brand' => 'Kerastase',
                'sku' => 'AMP-CAP',
            ],
            [
                'name' => 'Polvo Decolorante',
                'type' => 'consume_only',
                'base_unit' => 'g',
                'sale_price' => null,
                'stock_minimum' => 800,
                'category' => 'Coloracion',
                'brand' => 'Schwarzkopf',
                'sku' => 'POL-DEC',
            ],
        ];

        $items = collect($itemDefinitions)->map(function (array $data) use ($categoryMap, $brandMap) {
            $item = Item::query()->updateOrCreate(
                ['name' => $data['name']],
                [
                    'type' => $data['type'],
                    'base_unit' => $data['base_unit'],
                    'sale_price' => $data['sale_price'],
                    'stock_minimum' => $data['stock_minimum'],
                    'category_id' => $categoryMap[$data['category']]?->id,
                    'brand_id' => $brandMap[$data['brand']]?->id,
                    'sku' => $data['sku'] ?? null,
                    'barcode' => null,
                    'reorder_point' => max(0, ($data['stock_minimum'] ?? 0) * 0.6),
                    'reorder_qty' => max(0, ($data['stock_minimum'] ?? 0) * 1.2),
                    'is_active' => true,
                ],
            );

            $units = match ($data['base_unit']) {
                'ml' => [
                    ['unit' => 'ml', 'factor_to_base' => 1, 'is_base' => true],
                    ['unit' => 'l', 'factor_to_base' => 1000, 'is_base' => false],
                ],
                'g' => [
                    ['unit' => 'g', 'factor_to_base' => 1, 'is_base' => true],
                    ['unit' => 'kg', 'factor_to_base' => 1000, 'is_base' => false],
                ],
                default => [
                    ['unit' => $data['base_unit'], 'factor_to_base' => 1, 'is_base' => true],
                ],
            };

            $item->units()->delete();
            $item->units()->createMany($units);

            return $item;

        });
        $serviceCategories = collect([
            ['name' => 'Cortes de Cabello', 'sort_order' => 1],
            ['name' => 'Tratamientos Faciales', 'sort_order' => 2],
            ['name' => 'Coloracion', 'sort_order' => 3],
            ['name' => 'Peinados', 'sort_order' => 4],
            ['name' => 'Manicure y Pedicure', 'sort_order' => 5],
        ])->map(function (array $data) {
            return ServiceCategory::query()->updateOrCreate(
                ['name' => $data['name']],
                ['is_active' => true, 'sort_order' => $data['sort_order'] ?? 0],
            );
        });

        $locations = collect([
            ['name' => 'Salon Principal', 'address' => 'Santa Anita, Peru'],
            ['name' => 'Cabina Premium', 'address' => 'Santa Anita, Peru'],
        ])->map(function (array $data) {
            return Location::query()->updateOrCreate(
                ['name' => $data['name']],
                ['address' => $data['address'] ?? null, 'is_active' => true],
            );

        });

        $serviceCategoryMap = $serviceCategories->keyBy('name');

        $services = collect([
            [
                'name' => 'Corte Clasico',
                'base_price' => 50,
                'duration_min' => 45,
                'category' => 'Cortes de Cabello',
                'description' => 'Corte clasico con lavado y acabado.',
                'requires_deposit' => false,
                'deposit_amount' => null,
            ],
            [
                'name' => 'Corte Premium',
                'base_price' => 80,
                'duration_min' => 60,
                'category' => 'Cortes de Cabello',
                'description' => 'Corte personalizado con tratamiento express.',
                'requires_deposit' => false,
                'deposit_amount' => null,
            ],
            [
                'name' => 'Limpieza Facial',
                'base_price' => 100,
                'duration_min' => 50,
                'category' => 'Tratamientos Faciales',
                'description' => 'Limpieza profunda con hidratacion.',
                'requires_deposit' => false,
                'deposit_amount' => null,
            ],
            [
                'name' => 'Mascarilla Detox',
                'base_price' => 120,
                'duration_min' => 65,
                'category' => 'Tratamientos Faciales',
                'description' => 'Tratamiento facial con activos antioxidantes.',
                'requires_deposit' => true,
                'deposit_amount' => 40,
            ],
            [
                'name' => 'Color + Tratamiento',
                'base_price' => 150,
                'duration_min' => 120,
                'category' => 'Coloracion',
                'description' => 'Coloracion completa con tratamiento nutritivo.',
                'requires_deposit' => true,
                'deposit_amount' => 60,
            ],
            [
                'name' => 'Balayage',
                'base_price' => 220,
                'duration_min' => 150,
                'category' => 'Coloracion',
                'description' => 'Tecnica de iluminacion con matiz personalizado.',
                'requires_deposit' => true,
                'deposit_amount' => 80,
            ],
            [
                'name' => 'Brushing',
                'base_price' => 60,
                'duration_min' => 40,
                'category' => 'Peinados',
                'description' => 'Peinado con brushing y sellado.',
                'requires_deposit' => false,
                'deposit_amount' => null,
            ],
            [
                'name' => 'Peinado Evento',
                'base_price' => 180,
                'duration_min' => 90,
                'category' => 'Peinados',
                'description' => 'Peinado recogido para eventos especiales.',
                'requires_deposit' => true,
                'deposit_amount' => 50,
            ],
            [
                'name' => 'Manicure Clasico',
                'base_price' => 45,
                'duration_min' => 40,
                'category' => 'Manicure y Pedicure',
                'description' => 'Manicure con esmalte regular.',
                'requires_deposit' => false,
                'deposit_amount' => null,
            ],
        ])->map(function (array $data, int $index) use ($serviceCategoryMap) {
            return Service::query()->updateOrCreate(
                ['name' => $data['name']],
                [
                    'category_id' => $serviceCategoryMap[$data['category']]?->id,
                    'description' => $data['description'] ?? null,
                    'duration_min' => $data['duration_min'] ?? 60,
                    'base_price' => $data['base_price'],
                    'requires_deposit' => $data['requires_deposit'] ?? false,
                    'deposit_amount' => $data['deposit_amount'] ?? null,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );

        });
        foreach ($services as $service) {
            $locationIds = $locations->random(min(2, $locations->count()))->pluck('id')->all();
            $stylistIds = $stylists->random(min(3, $stylists->count()))->pluck('id')->all();
            $service->locations()->sync($locationIds);
            $service->stylists()->sync($stylistIds);
        }


        $purchaseService = app(PurchaseService::class);
        $saleService = app(SaleService::class);
        $serviceRecordService = app(ServiceRecordService::class);

        foreach ($items as $item) {
            $usesLiters = $item->units()->whereRaw('lower(unit) = ?', ['l'])->exists();
            $usesKg = $item->units()->whereRaw('lower(unit) = ?', ['kg'])->exists();

            $unit = $item->base_unit === 'und'
                ? 'und'
                : ($usesLiters ? 'l' : ($usesKg ? 'kg' : $item->base_unit));

            $quantity = $item->base_unit === 'und'
                ? 500
                : ($unit === 'l' ? 10 : ($unit === 'kg' ? 10 : 10000));

            $cost = $item->base_unit === 'und'
                ? 0.5
                : ($unit === 'l' ? 45 : ($unit === 'kg' ? 90 : 0.05));

            $purchaseService->create([
                'item_id' => $item->id,
                'quantity' => $quantity,
                'unit' => $unit,
                'cost_per_unit' => $cost,
                'notes' => 'Carga inicial',
                'supplier_id' => $suppliers->random()->id,
                'invoice_number' => 'FAC-' . rand(1000, 9999),
            ], $admin->id);

            $purchaseService->create([
                'item_id' => $item->id,
                'quantity' => $item->base_unit === 'und' ? 300 : 5,
                'unit' => $item->base_unit === 'und' ? 'und' : $item->base_unit,
                'cost_per_unit' => $item->base_unit === 'und' ? 0.6 : 0.08,
                'notes' => 'Compra adicional',
                'purchased_at' => now()->subDays(rand(2, 12)),
                'supplier_id' => $suppliers->random()->id,
                'invoice_number' => 'FAC-' . rand(1000, 9999),
            ], $admin->id);
        }

        $sellableItems = $items->filter(function (Item $item) {
            $type = $item->type?->value ?? $item->type;
            return $type !== 'consume_only';
        })->values();

        $consumableItems = $items->filter(function (Item $item) {
            $type = $item->type?->value ?? $item->type;
            return $type !== 'sell_only';
        })->values();

        for ($i = 0; $i < 40; $i++) {
            $saleLines = [];
            $lineCount = rand(1, 3);
            for ($j = 0; $j < $lineCount; $j++) {
                $item = $sellableItems->random();
                $saleLines[] = [
                    'item_id' => $item->id,
                    'quantity' => $item->base_unit === 'und' ? rand(1, 4) : rand(60, 180),
                    'unit' => $item->base_unit,
                    'unit_price' => $item->sale_price ?? 1,
                ];
            }

            $methods = ['cash', 'card', 'yape'];

            $saleService->create([
                'client_id' => $clients->random()->id,
                'payment_method' => $methods[array_rand($methods)],
                'sold_at' => now()->subDays(rand(0, 10))->setTime(rand(9, 19), rand(0, 59)),
                'items' => $saleLines,
            ], $admin->id);
        }

        for ($i = 0; $i < 60; $i++) {
            $service = $services->random();
            $stylist = $stylists->random();
            $client = $clients->random();

            $consumptions = [];
            $lineCount = rand(1, 3);
            for ($j = 0; $j < $lineCount; $j++) {
                $item = $consumableItems->random();
                $consumptions[] = [
                    'item_id' => $item->id,
                    'quantity' => $item->base_unit === 'und' ? 1 : rand(30, 120),
                    'unit' => $item->base_unit,
                ];
            }

            $methods = ['cash', 'card', 'yape'];

            $serviceRecordService->create([
                'service_id' => $service->id,
                'stylist_id' => $stylist->id,
                'client_id' => $client->id,
                'total_amount' => $service->base_price + rand(0, 60),
                'payment_method' => $methods[array_rand($methods)],
                'performed_at' => now()->subDays(rand(0, 10))->setTime(rand(9, 19), rand(0, 59)),
                'consumptions' => $consumptions,
            ], $admin->id);
        }

        // Garantizar data del dia para el dashboard
        $todaySaleItems = [
            [
                'item_id' => $sellableItems->first()->id,
                'quantity' => $sellableItems->first()->base_unit === 'und' ? 2 : 150,
                'unit' => $sellableItems->first()->base_unit,
                'unit_price' => $sellableItems->first()->sale_price ?? 1,
            ],
        ];

        $saleService->create([
            'client_id' => $clients->random()->id,
            'payment_method' => 'cash',
            'sold_at' => now(),
            'items' => $todaySaleItems,
        ], $admin->id);

        $service = $services->first();
        $stylist = $stylists->first();
        $client = $clients->first();
        $consumable = $consumableItems->first();

        $serviceRecordService->create([
            'service_id' => $service->id,
            'stylist_id' => $stylist->id,
            'client_id' => $client->id,
            'total_amount' => $service->base_price + 40,
            'payment_method' => 'card',
            'performed_at' => now(),
            'consumptions' => [
                [
                    'item_id' => $consumable->id,
                    'quantity' => $consumable->base_unit === 'und' ? 1 : 60,
                    'unit' => $consumable->base_unit,
                ],
            ],
        ], $admin->id);

        // Forzar al menos un item en stock bajo para reportes
        $lowItem = $items->first();
        $lowItem->update([
            'stock_minimum' => ($lowItem->stockLots()->sum('quantity_remaining') ?? 0) + 200,
        ]);

        $secondLow = $items->skip(1)->first();
        if ($secondLow) {
            $secondLow->update([
                'stock_minimum' => ($secondLow->stockLots()->sum('quantity_remaining') ?? 0) + 150,
            ]);
        }
    }
}


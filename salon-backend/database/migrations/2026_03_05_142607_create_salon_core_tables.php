<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stylists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 20);
            $table->string('base_unit', 20);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('stock_minimum', 12, 3)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('item_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('unit', 20);
            $table->decimal('factor_to_base', 12, 6);
            $table->boolean('is_base')->default(false);
            $table->timestamps();

            $table->unique(['item_id', 'unit']);
        });

        Schema::create('stock_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_remaining', 12, 3);
            $table->decimal('cost_per_base', 12, 4);
            $table->timestamp('purchased_at')->useCurrent();
            $table->date('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['item_id', 'purchased_at']);
        });

        Schema::create('inventory_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->decimal('quantity_base', 12, 3);
            $table->decimal('unit_cost_base', 12, 4)->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('moved_at')->useCurrent();
            $table->timestamps();

            $table->index(['item_id', 'moved_at']);
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('base_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stylist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('total_amount', 12, 2);
            $table->string('status', 20)->default('completed');
            $table->string('payment_method', 20)->nullable();
            $table->timestamp('performed_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('service_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_lot_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_base', 12, 3);
            $table->decimal('unit_cost_base', 12, 4);
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('total_amount', 12, 2);
            $table->string('status', 20)->default('completed');
            $table->string('payment_method', 20)->nullable();
            $table->timestamp('sold_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_lot_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_base', 12, 3);
            $table->decimal('unit_price_base', 12, 4);
            $table->decimal('unit_cost_base', 12, 4);
            $table->timestamps();
        });

        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_refund', 12, 2);
            $table->text('reason')->nullable();
            $table->timestamp('processed_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restocked_lot_id')->constrained('stock_lots')->cascadeOnDelete();
            $table->decimal('quantity_base', 12, 3);
            $table->decimal('unit_refund_base', 12, 4);
            $table->timestamps();
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stylist_id')->constrained()->cascadeOnDelete();
            $table->decimal('base_amount', 12, 2);
            $table->decimal('rate', 5, 2);
            $table->decimal('amount', 12, 2);
            $table->timestamp('calculated_at')->useCurrent();
            $table->timestamps();

            $table->unique('service_record_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('service_consumptions');
        Schema::dropIfExists('service_records');
        Schema::dropIfExists('services');
        Schema::dropIfExists('inventory_moves');
        Schema::dropIfExists('stock_lots');
        Schema::dropIfExists('item_units');
        Schema::dropIfExists('items');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('stylists');
    }
};

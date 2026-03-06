<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku', 60)->nullable()->unique();
            $table->string('barcode', 80)->nullable()->unique();
            $table->decimal('reorder_point', 12, 3)->default(0);
            $table->decimal('reorder_qty', 12, 3)->default(0);
        });

        Schema::table('stock_lots', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('lot_code', 60)->nullable();
            $table->string('invoice_number', 60)->nullable();
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_lots', function (Blueprint $table) {
            $table->dropIndex(['supplier_id']);
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropColumn(['lot_code', 'invoice_number']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropConstrainedForeignId('brand_id');
            $table->dropUnique(['sku']);
            $table->dropUnique(['barcode']);
            $table->dropColumn(['sku', 'barcode', 'reorder_point', 'reorder_qty']);
        });
    }
};

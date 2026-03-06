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
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('service_categories')->nullOnDelete()->after('id');
            $table->text('description')->nullable()->after('name');
            $table->unsignedInteger('duration_min')->default(60)->after('description');
            $table->boolean('requires_deposit')->default(false)->after('base_price');
            $table->decimal('deposit_amount', 12, 2)->nullable()->after('requires_deposit');
            $table->unsignedInteger('sort_order')->default(0)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'category_id',
                'description',
                'duration_min',
                'requires_deposit',
                'deposit_amount',
                'sort_order',
            ]);
        });
    }
};

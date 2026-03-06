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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('full_name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('dni', 30)->nullable()->after('email');
            $table->string('address')->nullable()->after('dni');
            $table->date('birth_date')->nullable()->after('address');
            $table->string('gender', 20)->nullable()->after('birth_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'dni', 'address', 'birth_date', 'gender']);
        });
    }
};

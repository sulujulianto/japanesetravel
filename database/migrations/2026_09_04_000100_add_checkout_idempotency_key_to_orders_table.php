<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->char('checkout_idempotency_key', 64)->nullable()->after('user_id');
            $table->unique('checkout_idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['checkout_idempotency_key']);
            $table->dropColumn('checkout_idempotency_key');
        });
    }
};

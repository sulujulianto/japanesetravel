<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('shipping_address_id')
                ->nullable()
                ->after('user_id')
                ->constrained('user_addresses')
                ->nullOnDelete();
            $table->text('shipping_address_snapshot')->nullable()->after('shipping_address_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('shipping_address_id');
            $table->dropColumn('shipping_address_snapshot');
        });
    }
};

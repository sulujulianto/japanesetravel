<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->index('updated_at', 'payments_payload_retention_index');
        });

        Schema::table('payment_webhook_events', function (Blueprint $table): void {
            $table->index('created_at', 'payment_webhook_events_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex('payments_payload_retention_index');
        });

        Schema::table('payment_webhook_events', function (Blueprint $table): void {
            $table->dropIndex('payment_webhook_events_created_at_index');
        });
    }
};

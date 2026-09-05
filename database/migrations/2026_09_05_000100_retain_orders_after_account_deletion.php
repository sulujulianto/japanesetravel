<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->text('customer_snapshot')->nullable()->after('shipping_address_snapshot');
        });

        DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select([
                'orders.id as order_id',
                'users.username',
                'users.email',
            ])
            ->orderBy('orders.id')
            ->chunkById(500, function ($orders): void {
                foreach ($orders as $order) {
                    $snapshot = json_encode([
                        'username' => (string) $order->username,
                        'email' => (string) $order->email,
                    ], JSON_THROW_ON_ERROR);

                    DB::table('orders')
                        ->where('id', $order->order_id)
                        ->update(['customer_snapshot' => Crypt::encryptString($snapshot)]);
                }
            }, 'orders.id', 'order_id');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('orders')->whereNull('user_id')->exists()) {
            throw new RuntimeException('Cannot restore cascading user deletion while retained orders have no user.');
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->dropColumn('customer_snapshot');
        });
    }
};

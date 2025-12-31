<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::table('orders')
                ->where('status', 'paid')
                ->update(['status' => 'processing']);

            DB::table('orders')
                ->where('status', 'shipped')
                ->update(['status' => 'completed']);

            return;
        }

        DB::table('orders')
            ->where('status', 'paid')
            ->update(['status' => 'processing']);

        DB::table('orders')
            ->where('status', 'shipped')
            ->update(['status' => 'completed']);

        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','paid','shipped','completed','cancelled') NOT NULL DEFAULT 'pending'");

        DB::table('orders')
            ->where('status', 'processing')
            ->update(['status' => 'paid']);
    }
};

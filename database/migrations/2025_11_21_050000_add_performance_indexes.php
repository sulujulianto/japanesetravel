<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureIndex('orders', 'user_id', 'orders_user_id_index');
        $this->ensureIndex('orders', 'created_at', 'orders_created_at_index');
        $this->ensureIndex('orders', 'status', 'orders_status_index');
        $this->ensureIndex('payments', 'order_id', 'payments_order_id_index');
        $this->ensureIndex('payments', 'status', 'payments_status_index');
        $this->ensureIndex('order_items', 'order_id', 'order_items_order_id_index');
        $this->ensureIndex('souvenirs', 'stock', 'souvenirs_stock_index');
        $this->ensureIndex('place_reviews', 'place_id', 'place_reviews_place_id_index');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('orders', 'orders_user_id_index');
        $this->dropIndexIfExists('orders', 'orders_created_at_index');
        $this->dropIndexIfExists('orders', 'orders_status_index');
        $this->dropIndexIfExists('payments', 'payments_order_id_index');
        $this->dropIndexIfExists('payments', 'payments_status_index');
        $this->dropIndexIfExists('order_items', 'order_items_order_id_index');
        $this->dropIndexIfExists('souvenirs', 'souvenirs_stock_index');
        $this->dropIndexIfExists('place_reviews', 'place_reviews_place_id_index');
    }

    protected function ensureIndex(string $table, string $column, string $indexName): void
    {
        if ($this->columnHasIndex($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $indexName) {
            $blueprint->index($column, $indexName);
        });
    }

    protected function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropIndex($indexName);
        });
    }

    protected function columnHasIndex(string $table, string $column): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('$table')");
            foreach ($indexes as $index) {
                $columns = DB::select("PRAGMA index_info('{$index->name}')");
                foreach ($columns as $col) {
                    if ($col->name === $column) {
                        return true;
                    }
                }
            }

            return false;
        }

        $database = DB::getDatabaseName();
        $result = DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND column_name = ? LIMIT 1',
            [$database, $table, $column]
        );

        return ! empty($result);
    }

    protected function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('$table')");
            foreach ($indexes as $index) {
                if ($index->name === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $database = DB::getDatabaseName();
        $result = DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $indexName]
        );

        return ! empty($result);
    }
};

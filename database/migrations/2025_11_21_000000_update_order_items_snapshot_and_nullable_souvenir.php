<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('order_items', function (Blueprint $table) {
                if (! Schema::hasColumn('order_items', 'product_name')) {
                    $table->string('product_name')->default('')->after('souvenir_id');
                }
                if (! Schema::hasColumn('order_items', 'product_price')) {
                    $table->decimal('product_price', 12, 2)->default(0)->after('product_name');
                }
                if (! Schema::hasColumn('order_items', 'product_image')) {
                    $table->string('product_image')->nullable()->after('product_price');
                }
            });

            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['souvenir_id']);
        });

        DB::statement('ALTER TABLE order_items MODIFY souvenir_id BIGINT UNSIGNED NULL');

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreign('souvenir_id')->references('id')->on('souvenirs')->nullOnDelete();
            $table->string('product_name')->default('')->after('souvenir_id');
            $table->decimal('product_price', 12, 2)->default(0)->after('product_name');
            $table->string('product_image')->nullable()->after('product_price');
        });

        DB::table('order_items')
            ->join('souvenirs', 'order_items.souvenir_id', '=', 'souvenirs.id')
            ->update([
                'order_items.product_name' => DB::raw('souvenirs.name'),
                'order_items.product_price' => DB::raw('souvenirs.price'),
                'order_items.product_image' => DB::raw('souvenirs.image'),
            ]);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('order_items', function (Blueprint $table) {
                if (Schema::hasColumn('order_items', 'product_name')) {
                    $table->dropColumn(['product_name', 'product_price', 'product_image']);
                }
            });

            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['souvenir_id']);
            $table->dropColumn(['product_name', 'product_price', 'product_image']);
        });

        DB::statement('ALTER TABLE order_items MODIFY souvenir_id BIGINT UNSIGNED NOT NULL');

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreign('souvenir_id')->references('id')->on('souvenirs')->onDelete('cascade');
        });
    }
};

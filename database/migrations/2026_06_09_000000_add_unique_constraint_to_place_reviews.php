<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX_NAME = 'place_reviews_place_id_user_id_unique';

    public function up(): void
    {
        Schema::table('place_reviews', function (Blueprint $table): void {
            $table->unique(['place_id', 'user_id'], self::INDEX_NAME);
        });
    }

    public function down(): void
    {
        Schema::table('place_reviews', function (Blueprint $table): void {
            $table->dropUnique(self::INDEX_NAME);
        });
    }
};

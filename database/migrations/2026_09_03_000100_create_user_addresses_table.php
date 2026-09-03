<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 50);
            $table->text('recipient_name');
            $table->text('recipient_phone');
            $table->text('address_line_1');
            $table->text('address_line_2')->nullable();
            $table->text('city');
            $table->text('province');
            $table->text('postal_code');
            $table->char('country_code', 2)->default('ID');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default'], 'user_addresses_user_default_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};

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
        Schema::create('souvenirs', function (Blueprint $table) {
            $table->id();
            $table->string('name');             // Nama Barang
            $table->text('description')->nullable(); // Deskripsi
            $table->decimal('price', 12, 2);    // Harga (Format uang)
            $table->integer('stock')->default(0); // Stok barang
            $table->string('image')->nullable(); // Foto barang
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('souvenirs');
    }
};
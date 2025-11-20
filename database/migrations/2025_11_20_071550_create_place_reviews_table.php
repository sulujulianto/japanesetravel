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
        Schema::create('place_reviews', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel places (Ulasan ini milik wisata apa?)
            // onDelete('cascade') artinya jika wisata dihapus, ulasannya ikut terhapus.
            $table->foreignId('place_id')->constrained('places')->onDelete('cascade');
            
            // Relasi ke tabel users (Siapa yang nulis?)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->unsignedTinyInteger('rating'); // Angka 1 - 5
            $table->text('comment')->nullable();   // Komentar (boleh kosong)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place_reviews');
    }
};
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
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 180)->unique(); // Untuk URL cantik (misal: /place/tokyo-tower)
            $table->text('description')->nullable();
            $table->string('image')->nullable(); // Foto utama
            $table->string('video_url')->nullable();
            $table->text('gallery')->nullable(); // Kita simpan daftar foto lain di sini
            $table->string('address')->nullable();
            $table->text('facilities')->nullable();
            $table->string('open_days', 100)->nullable(); // Contoh: "Senin - Jumat"
            $table->string('open_hours', 100)->nullable(); // Contoh: "09:00 - 22:00"
            $table->json('opening_hours')->nullable(); // Jam buka detail per hari
            
            // Relasi ke tabel users (siapa yang input data ini)
            // Jika user dihapus, data wisata tidak hilang (set null)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps(); // Created_at & Updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
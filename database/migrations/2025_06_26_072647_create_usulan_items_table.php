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
        Schema::create('usulan_items', function (Blueprint $table) {
            $table->id();

            // --- PERBAIKAN DI SINI ---
            // Mengubah foreignUuid menjadi foreignId agar cocok dengan tabel usulans
            $table->foreignId('usulan_id')->constrained('usulans')->onDelete('cascade');

            $table->string('nama_barang');
            $table->integer('jumlah')->default(1);
            $table->string('satuan');
            $table->text('spesifikasi')->nullable();
            $table->decimal('perkiraan_harga', 15, 2)->nullable(); // Opsional: harga per unit

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usulan_items');
    }
};

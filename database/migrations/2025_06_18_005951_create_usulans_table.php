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
        Schema::create('usulans', function (Blueprint $table) {
            // Kolom-kolom utama
            $table->id();
            $table->string('nama_barang');
            $table->text('deskripsi')->nullable();
            $table->string('lampiran')->nullable()->comment('Path ke file foto atau PDF');
            $table->date('tanggal_usulan');

            $table->string('status')->default('diajukan')->comment('Contoh: diajukan, ditolak_taop, diteruskan_pimpinan, disetujui, dll.');

            // KOLOM RELASI UTAMA (PENYESUAIAN)
            // Menggunakan foreignUuid agar cocok dengan tabel 'users' yang primary key-nya UUID
            $table->foreignUuid('user_id')->constrained('users')->comment('User yang mengajukan usulan');
            $table->foreignId('lab_id')->constrained()->comment('Lab yang dituju untuk usulan ini');

            // KOLOM BARU (PENYESUAIAN)
            // Kolom ini juga harus UUID karena merujuk ke tabel 'users'
            $table->foreignUuid('diperiksa_oleh_id')->nullable()->constrained('users')->comment('User ID (TAOP/Pimpinan) yang memeriksa usulan');
            $table->timestamp('tanggal_pemeriksaan')->nullable()->comment('Waktu saat usulan diperiksa');

            // KOLOM LAMA (tidak ada di sini, hanya contoh jika ada)
            // $table->text('catatan_revisi')->nullable()->comment('Diisi oleh TAOP jika usulan ditolak/perlu revisi');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usulans');
    }
};

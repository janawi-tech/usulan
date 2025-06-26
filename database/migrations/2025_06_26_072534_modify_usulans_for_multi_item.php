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
        Schema::table('usulans', function (Blueprint $table) {
            // Cek jika kolom 'judul_usulan' BELUM ADA, maka tambahkan
            if (!Schema::hasColumn('usulans', 'judul_usulan')) {
                $table->string('judul_usulan')->after('id');
            }

            // Cek jika kolom 'nama_barang' MASIH ADA, maka hapus
            if (Schema::hasColumn('usulans', 'nama_barang')) {
                $table->dropColumn('nama_barang');
            }

            // Cek jika kolom 'deskripsi' MASIH ADA, maka hapus
            if (Schema::hasColumn('usulans', 'deskripsi')) {
                $table->dropColumn('deskripsi');
            }

            // Cek jika kolom 'lampiran' MASIH ADA, maka hapus
            if (Schema::hasColumn('usulans', 'lampiran')) {
                $table->dropColumn('lampiran');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usulans', function (Blueprint $table) {
            // Logika untuk mengembalikan jika perlu
            if (Schema::hasColumn('usulans', 'judul_usulan')) {
                $table->dropColumn('judul_usulan');
            }
            if (!Schema::hasColumn('usulans', 'nama_barang')) {
                $table->string('nama_barang')->after('id');
                $table->text('deskripsi')->nullable()->after('nama_barang');
                $table->string('lampiran')->nullable()->after('deskripsi');
            }
        });
    }
};

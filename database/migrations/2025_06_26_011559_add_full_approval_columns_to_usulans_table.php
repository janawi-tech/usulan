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
            // Menambahkan semua kolom untuk alur Adum & Pimpinan sekaligus

            // Kolom untuk Adum
            $table->foreignUuid('adum_user_id')->nullable()->after('tanggal_pemeriksaan')->constrained('users');
            $table->timestamp('adum_approved_at')->nullable()->after('adum_user_id');
            $table->text('catatan_adum')->nullable()->after('adum_approved_at');

            // Kolom untuk Pimpinan
            $table->foreignUuid('pimpinan_user_id')->nullable()->after('catatan_adum')->constrained('users');
            $table->timestamp('pimpinan_approved_at')->nullable()->after('pimpinan_user_id');
            $table->text('catatan_pimpinan')->nullable()->after('pimpinan_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usulans', function (Blueprint $table) {
            // Urutan pelepasan constraint harus benar
            $table->dropForeign(['adum_user_id']);
            $table->dropForeign(['pimpinan_user_id']);

            $table->dropColumn([
                'adum_user_id',
                'adum_approved_at',
                'catatan_adum',
                'pimpinan_user_id',
                'pimpinan_approved_at',
                'catatan_pimpinan',
            ]);
        });
    }
};

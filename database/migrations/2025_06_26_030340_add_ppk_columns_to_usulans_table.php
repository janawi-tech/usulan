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
            // Kolom untuk mencatat siapa PPK yang memproses
            $table->foreignUuid('ppk_user_id')->nullable()->after('catatan_pimpinan')->constrained('users');

            // Kolom untuk mencatat KAPAN PPK memproses
            $table->timestamp('ppk_processed_at')->nullable()->after('ppk_user_id');

            // Kolom untuk menyimpan catatan dari PPK (misal, alasan penundaan)
            $table->text('catatan_ppk')->nullable()->after('ppk_processed_at');

            // Kolom untuk menyimpan tanggal sampai kapan usulan ditunda
            $table->date('ditunda_hingga')->nullable()->after('catatan_ppk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usulans', function (Blueprint $table) {
            $table->dropForeign(['ppk_user_id']);
            $table->dropColumn([
                'ppk_user_id',
                'ppk_processed_at',
                'catatan_ppk',
                'ditunda_hingga',
            ]);
        });
    }
};

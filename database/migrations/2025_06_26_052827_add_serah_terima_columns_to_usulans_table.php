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
            // Kolom untuk menyimpan path file bukti serah terima (BAST)
            $table->string('bukti_serah_terima')->nullable()->after('ditunda_hingga');

            // Kolom untuk menyimpan catatan saat serah terima
            $table->text('catatan_serah_terima')->nullable()->after('bukti_serah_terima');

            // Kolom untuk mencatat kapan barang diserahkan
            $table->timestamp('serah_terima_at')->nullable()->after('catatan_serah_terima');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usulans', function (Blueprint $table) {
            $table->dropColumn([
                'bukti_serah_terima',
                'catatan_serah_terima',
                'serah_terima_at',
            ]);
        });
    }
};

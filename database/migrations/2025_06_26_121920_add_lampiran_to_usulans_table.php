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
            // Menambahkan kembali kolom lampiran untuk dokumen pendukung keseluruhan usulan
            if (!Schema::hasColumn('usulans', 'lampiran')) {
                $table->string('lampiran')->nullable()->after('judul_usulan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usulans', function (Blueprint $table) {
            if (Schema::hasColumn('usulans', 'lampiran')) {
                $table->dropColumn('lampiran');
            }
        });
    }
};

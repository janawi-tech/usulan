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
            // Mengubah tipe kolom lampiran menjadi JSON untuk menyimpan banyak file
            $table->json('lampiran')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usulans', function (Blueprint $table) {
            // Mengembalikan tipe kolom ke string jika di-rollback
            $table->string('lampiran')->nullable()->change();
        });
    }
};

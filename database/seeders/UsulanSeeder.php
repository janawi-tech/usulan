<?php

namespace Database\Seeders;

use App\Models\Lab;
use App\Models\Usulan;
use Illuminate\Database\Seeder;

class UsulanSeeder extends Seeder
{
    public function run(): void
    {
        Usulan::create([
            'judul' => 'Pengadaan Mikroskop',
            'deskripsi' => 'Membeli mikroskop untuk praktikum mahasiswa',
            'anggaran' => 50000000,
            'pengusul' => 'Dr. Ahmad Suryadi',
            'lab_id' => '1',
            'status' => 'draft',
        ]);

        // Buat usulan untuk lab fisika
        Usulan::create([
            'judul' => 'Pelatihan Safety Lab',
            'deskripsi' => 'Pelatihan keselamatan kerja di laboratorium',
            'anggaran' => 15000000,
            'pengusul' => 'Prof. Siti Nurhaliza',
            'lab_id' => '2',
            'status' => 'diajukan',
        ]);
    }
}

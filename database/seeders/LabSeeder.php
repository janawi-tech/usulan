<?php

namespace Database\Seeders;

use App\Models\Lab;
use Illuminate\Database\Seeder;

class LabSeeder extends Seeder
{
    public function run(): void
    {
        $labKimia = Lab::create([
            'nama_lab' => 'Laboratorium Kimia',
            'fakultas' => 'MIPA',
            'kepala_lab' => 'Dr. Ahmad Suryadi',
        ]);

        $labFisika = Lab::create([
            'nama_lab' => 'Laboratorium Fisika',
            'fakultas' => 'MIPA',
            'kepala_lab' => 'Prof. Siti Nurhaliza',
        ]);
    }
}

<?php

namespace App\Filament\Resources\UsulanResource\Pages;

use App\Filament\Resources\UsulanResource;
use App\Models\Usulan; // <-- Import Model Usulan
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder; // <-- Import Builder

class ListUsulans extends ListRecords
{
    protected static string $resource = UsulanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Mendefinisikan tab filter di atas tabel.
     */
    public function getTabs(): array
    {
        // Menghitung jumlah untuk setiap badge
        $ditolakCount = Usulan::where('status', 'like', '%ditolak%')->count();
        $diajukanCount = Usulan::where('status', 'diajukan')->count();
        $disetujuiCount = Usulan::where('status', 'disetujui')->count();

        return [
            'semua' => ListRecords\Tab::make('Semua'),

            'diajukan' => ListRecords\Tab::make('Diajukan')
                ->badge($diajukanCount)
                ->badgeColor('warning')
                ->query(fn(Builder $query) => $query->where('status', 'diajukan')),

            // --- INI BAGIAN PENTINGNYA ---
            'ditolak' => ListRecords\Tab::make('Ditolak')
                ->badge($ditolakCount > 0 ? $ditolakCount : null) // Tampilkan badge jika ada data
                ->badgeColor('danger')
                ->query(fn(Builder $query) => $query->where('status', 'like', '%ditolak%')),

            'disetujui' => ListRecords\Tab::make('Disetujui')
                ->badge($disetujuiCount > 0 ? $disetujuiCount : null)
                ->badgeColor('success')
                ->query(fn(Builder $query) => $query->where('status', 'disetujui')),
        ];
    }
}

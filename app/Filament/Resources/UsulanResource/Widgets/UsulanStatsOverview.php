<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UsulanResource;
use App\Models\Usulan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class UsulanStatsOverview extends BaseWidget
{
    /**
     * Tentukan apakah widget ini harus ditampilkan.
     * Hanya akan tampil jika user memiliki peran yang sesuai.
     */
    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['TAOP', 'Super Admin', 'Admin', 'super_admin']);
    }

    protected function getStats(): array
    {
        // Hitung jumlah usulan berdasarkan status
        $diajukanCount = Usulan::where('status', 'diajukan')->count();
        $diteruskanCount = Usulan::where('status', 'diteruskan_pimpinan')->count();
        $disetujuiCount = Usulan::where('status', 'disetujui')->count();

        return [
            Stat::make('Menunggu Persetujuan TAOP', $diajukanCount)
                ->description('Usulan baru yang perlu diperiksa')
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color('warning')
                // Buat agar bisa diklik dan menuju ke halaman Usulan yang difilter
                ->url(UsulanResource::getUrl('index', ['tableFilters[status][value]' => 'diajukan'])),

            Stat::make('Diteruskan ke Pimpinan', $diteruskanCount)
                ->description('Usulan yang menunggu persetujuan Pimpinan')
                ->descriptionIcon('heroicon-m-arrow-up-on-square')
                ->color('info')
                ->url(UsulanResource::getUrl('index', ['tableFilters[status][value]' => 'diteruskan_pimpinan'])),

            Stat::make('Total Usulan Disetujui', $disetujuiCount)
                ->description('Usulan yang telah disetujui sepenuhnya')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}

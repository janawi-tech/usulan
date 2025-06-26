<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UsulanResource;
use App\Models\Lab;
use App\Models\Usulan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class RoleBasedStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $stats = [];
        $managementRoles = ['Super Admin', 'Admin', 'super_admin', 'TAOP', 'ADUM', 'PIMPINAN', 'PPK'];

        // --- Logika untuk Pengguna Lab ---
        if (!$user->hasAnyRole($managementRoles)) {
            $myPendingCount = Usulan::where('user_id', $user->id)->whereIn('status', ['diajukan', 'diteruskan_adum', 'diteruskan_pimpinan'])->count();
            $myRejectedCount = Usulan::where('user_id', $user->id)->where('status', 'like', '%ditolak%')->count();
            $myDoneCount = Usulan::where('user_id', $user->id)->whereIn('status', ['disetujui', 'direalisasikan', 'selesai'])->count();

            return [
                Stat::make('Usulan Saya (Diproses)', $myPendingCount)->icon('heroicon-m-arrow-path'),
                Stat::make('Usulan Saya (Ditolak)', $myRejectedCount)->icon('heroicon-m-x-circle')->color('danger'),
                Stat::make('Usulan Saya (Selesai)', $myDoneCount)->icon('heroicon-m-check-badge')->color('success'),
            ];
        }

        // --- Logika untuk Pengguna Manajemen ---

        // === Dashboard untuk TAOP ===
        if ($user->hasAnyRole(['TAOP', 'Super Admin', 'Admin', 'super_admin'])) {
            $stats = array_merge($stats, [
                Stat::make('Menunggu Persetujuan TAOP', Usulan::where('status', 'diajukan')->count())
                    ->icon('heroicon-m-inbox-arrow-down')->color('info')->url(UsulanResource::getUrl('index', ['tableFilters[status][value]' => 'diajukan'])),
                Stat::make('Sudah Diteruskan ke Adum', Usulan::where('status', 'diteruskan_adum')->count())
                    ->icon('heroicon-m-arrow-up-on-square')->color('gray')->url(UsulanResource::getUrl('index', ['tableFilters[status][value]' => 'diteruskan_adum'])),
                Stat::make('Total Ditolak TAOP', Usulan::where('status', 'ditolak_taop')->count())
                    ->icon('heroicon-m-x-circle')->color('danger')->url(UsulanResource::getUrl('index', ['tableFilters[status][value]' => 'ditolak_taop'])),
            ]);
        }

        // === Dashboard untuk ADUM ===
        if ($user->hasAnyRole(['ADUM', 'Super Admin', 'Admin', 'super_admin'])) {
            $stats = array_merge($stats, [
                Stat::make('Menunggu Persetujuan Adum', Usulan::where('status', 'diteruskan_adum')->count())
                    ->icon('heroicon-m-inbox-arrow-down')->color('info')->url(UsulanResource::getUrl('index', ['tableFilters[status][value]' => 'diteruskan_adum'])),
                Stat::make('Sudah Diteruskan ke Pimpinan', Usulan::where('status', 'diteruskan_pimpinan')->count())
                    ->icon('heroicon-m-arrow-up-on-square')->color('gray')->url(UsulanResource::getUrl('index', ['tableFilters[status][value]' => 'diteruskan_pimpinan'])),
                Stat::make('Total Ditolak Adum', Usulan::where('status', 'ditolak_adum')->count())
                    ->icon('heroicon-m-x-circle')->color('danger')->url(UsulanResource::getUrl('index', ['tableFilters[status][value]' => 'ditolak_adum'])),
            ]);
        }

        // === Dashboard untuk PIMPINAN ===
        if ($user->hasAnyRole(['PIMPINAN', 'Super Admin', 'Admin', 'super_admin'])) {
            $stats = array_merge($stats, [
                Stat::make('Menunggu Persetujuan Pimpinan', Usulan::where('status', 'diteruskan_pimpinan')->count())
                    ->icon('heroicon-m-inbox-arrow-down')->color('warning')->url(UsulanResource::getUrl('index', ['tableFilters[status][value]' => 'diteruskan_pimpinan'])),
                Stat::make('Sudah Disetujui (Final)', Usulan::where('status', 'disetujui')->count())
                    ->icon('heroicon-m-check-badge')->color('success')->url(UsulanResource::getUrl('index', ['tableFilters[status][value]' => 'disetujui'])),
                Stat::make('Total Ditolak Pimpinan', Usulan::where('status', 'ditolak_pimpinan')->count())
                    ->icon('heroicon-m-x-circle')->color('danger')->url(UsulanResource::getUrl('index', ['tableFilters[status][value]' => 'ditolak_pimpinan'])),
            ]);
        }

        // === Dashboard untuk PPK ===
        if ($user->hasAnyRole(['PPK', 'Super Admin', 'Admin', 'super_admin'])) {
            $stats = array_merge($stats, [
                Stat::make('Siap Direalisasi', Usulan::where('status', 'disetujui')->count())
                    ->icon('heroicon-m-inbox-arrow-down')->color('success')->url(UsulanResource::getUrl('index', ['tableFilters[status][value]' => 'disetujui'])),
                Stat::make('Usulan Ditunda', Usulan::where('status', 'ditunda')->count())
                    ->icon('heroicon-m-pause-circle')->color('warning')->url(UsulanResource::getUrl('index', ['tableFilters[status][value]' => 'ditunda'])),
                Stat::make('Usulan Selesai', Usulan::where('status', 'selesai')->count())
                    ->icon('heroicon-m-check-badge')->color('success')->url(UsulanResource::getUrl('index', ['tableFilters[status][value]' => 'selesai'])),
            ]);
        }

        // Menghapus duplikat kartu jika user punya banyak peran (misal Super Admin)
        $uniqueStats = [];
        foreach ($stats as $stat) {
            $uniqueStats[$stat->getLabel()] = $stat;
        }

        return array_values($uniqueStats);
    }
}

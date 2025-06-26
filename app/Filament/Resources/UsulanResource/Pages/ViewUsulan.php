<?php

namespace App\Filament\Resources\UsulanResource\Pages;

use App\Filament\Resources\UsulanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ViewUsulan extends ViewRecord
{
    protected static string $resource = UsulanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn($record) => in_array($record->status, ['diajukan', 'ditolak_taop'])),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // -- BAGIAN 1: DETAIL UTAMA --
                Components\Section::make('Informasi Usulan')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('nama_barang')->label('Nama Barang'),
                                Components\TextEntry::make('lab.nama_lab')->label('Diajukan oleh Lab'),
                                Components\TextEntry::make('deskripsi')->columnSpanFull(),
                                // --- PERUBAHAN DI SINI (LAMPIRAN) ---
                                Components\Group::make([
                                    // Menggunakan ViewEntry untuk gambar yang bisa di-zoom
                                    Components\ViewEntry::make('lampiran')
                                        ->label('Lampiran Gambar')
                                        ->view('filament.infolists.components.zoomable-image')
                                        ->visible(function ($state) {
                                            if (!$state) return false;
                                            $path = storage_path('app/public/' . $state);
                                            return file_exists($path) && Str::startsWith(mime_content_type($path), 'image');
                                        }),
                                    Components\TextEntry::make('lampiran')
                                        ->label('Lampiran Dokumen')
                                        ->icon('heroicon-o-document-text')
                                        ->url(fn($state) => $state ? url('storage/' . $state) : null, true)
                                        ->visible(function ($state) {
                                            if (!$state) return false;
                                            $path = storage_path('app/public/' . $state);
                                            return file_exists($path) && !Str::startsWith(mime_content_type($path), 'image');
                                        }),
                                ])->columnSpanFull(),
                            ]),
                    ]),

                // -- BAGIAN 2: TIMELINE PERSETUJUAN --
                Components\Section::make('Riwayat Persetujuan')
                    ->schema([
                        Components\Fieldset::make('Tahap 1: Pengajuan Lab')
                            ->schema([
                                Components\TextEntry::make('user.firstname')->label('Diajukan oleh')
                                    ->formatStateUsing(fn(Model $record) => "{$record->user->firstname} {$record->user->lastname}"),
                                Components\TextEntry::make('tanggal_usulan')->label('Pada Tanggal')->date('d F Y'),
                            ]),

                        Components\Fieldset::make('Tahap 2: Persetujuan TAOP')
                            ->schema([
                                Components\TextEntry::make('pemeriksa.firstname')->label('Diperiksa oleh')
                                    ->formatStateUsing(fn(Model $record) => "{$record->pemeriksa->firstname} {$record->pemeriksa->lastname}"),
                                Components\TextEntry::make('status')
                                    ->label('Keputusan')
                                    ->badge()
                                    ->color(fn(string $state): string => $state === 'ditolak_taop' ? 'danger' : 'success')
                                    ->formatStateUsing(fn(string $state): string => $state === 'ditolak_taop' ? 'Ditolak untuk Revisi' : 'Diteruskan ke Adum'),
                                Components\TextEntry::make('catatan_revisi')->label('Catatan Revisi')->visible(fn($state) => !empty($state)),
                            ])
                            ->visible(fn(Model $record) => !is_null($record->diperiksa_oleh_id)),

                        Components\Fieldset::make('Tahap 3: Persetujuan ADUM')
                            ->schema([
                                Components\TextEntry::make('adum.firstname')->label('Diperiksa oleh')
                                    ->formatStateUsing(fn(Model $record) => "{$record->adum->firstname} {$record->adum->lastname}"),
                                Components\TextEntry::make('status')
                                    ->label('Keputusan')
                                    ->badge()
                                    ->color(fn(string $state): string => $state === 'ditolak_adum' ? 'danger' : 'success')
                                    ->formatStateUsing(fn(string $state): string => $state === 'ditolak_adum' ? 'Ditolak' : 'Diteruskan ke Pimpinan'),
                                Components\TextEntry::make('catatan_adum')->label('Catatan Penolakan')->visible(fn($state) => !empty($state)),
                            ])
                            ->visible(fn(Model $record) => !is_null($record->adum_user_id)),

                        Components\Fieldset::make('Tahap 4: Persetujuan Pimpinan')
                            ->schema([
                                Components\TextEntry::make('pimpinan.firstname')->label('Diperiksa oleh')
                                    ->formatStateUsing(fn(Model $record) => "{$record->pimpinan->firstname} {$record->pimpinan->lastname}"),
                                Components\TextEntry::make('status')
                                    ->label('Keputusan')
                                    ->badge()
                                    ->color(fn(string $state): string => $state === 'ditolak_pimpinan' ? 'danger' : 'success')
                                    ->formatStateUsing(fn(string $state): string => $state === 'ditolak_pimpinan' ? 'Ditolak' : 'Disetujui untuk diproses'),
                                Components\TextEntry::make('catatan_pimpinan')->label('Catatan Penolakan')->visible(fn($state) => !empty($state)),
                            ])
                            ->visible(fn(Model $record) => !is_null($record->pimpinan_user_id)),

                        Components\Fieldset::make('Tahap 5: Proses PPK')
                            ->schema([
                                Components\TextEntry::make('ppk.firstname')->label('Diproses oleh')
                                    ->formatStateUsing(fn(Model $record) => "{$record->ppk->firstname} {$record->ppk->lastname}"),
                                Components\TextEntry::make('status')
                                    ->label('Keputusan')
                                    ->badge()
                                    ->color(fn(string $state): string => $state === 'ditunda' ? 'warning' : 'success')
                                    ->formatStateUsing(fn(string $state): string => $state === 'ditunda' ? 'Ditunda' : 'Direalisasikan'),
                                Components\TextEntry::make('catatan_ppk')->label('Catatan')->visible(fn($state) => !empty($state)),
                                Components\TextEntry::make('ditunda_hingga')->label('Ditunda Hingga')->date('d F Y')->visible(fn($state) => !is_null($state)),
                            ])
                            ->visible(fn(Model $record) => !is_null($record->ppk_user_id) && $record->status !== 'selesai'),

                        // --- PERUBAHAN DI SINI (SERAH TERIMA) ---
                        Components\Fieldset::make('Tahap 6: Serah Terima Barang')
                            ->schema([
                                Components\Grid::make(2)->schema([
                                    Components\TextEntry::make('ppk.firstname')->label('Diserahkan oleh')
                                        ->formatStateUsing(fn(Model $record) => "{$record->ppk->firstname} {$record->ppk->lastname}"),
                                    Components\TextEntry::make('serah_terima_at')->label('Tanggal Serah Terima')->dateTime('d F Y, H:i'),
                                ]),
                                Components\TextEntry::make('catatan_serah_terima')->label('Catatan')->visible(fn($state) => !empty($state)),
                                Components\Group::make([
                                    // Menggunakan ViewEntry untuk bukti serah terima yang bisa di-zoom
                                    Components\ViewEntry::make('bukti_serah_terima')
                                        ->label('Bukti Serah Terima (Gambar)')
                                        ->view('filament.infolists.components.zoomable-image')
                                        ->visible(function ($state) {
                                            if (!$state) return false;
                                            $path = storage_path('app/public/' . $state);
                                            return file_exists($path) && Str::startsWith(mime_content_type($path), 'image');
                                        }),
                                    Components\TextEntry::make('bukti_serah_terima')
                                        ->label('Bukti Serah Terima (Dokumen)')
                                        ->icon('heroicon-o-document-text')
                                        ->url(fn($state) => $state ? url('storage/' . $state) : null, true)
                                        ->visible(function ($state) {
                                            if (!$state) return false;
                                            $path = storage_path('app/public/' . $state);
                                            return file_exists($path) && !Str::startsWith(mime_content_type($path), 'image');
                                        }),
                                ])->columnSpanFull(),
                            ])
                            ->visible(fn(Model $record) => $record->status === 'selesai'),

                    ])
                    ->collapsible(),
            ]);
    }
}

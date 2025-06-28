<?php

namespace App\Filament\Resources\UsulanResource\Pages;

use App\Filament\Resources\UsulanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

class ViewUsulan extends ViewRecord
{
    protected static string $resource = UsulanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Cetak PDF')
                ->color('success')
                ->icon('heroicon-o-printer')
                ->action(function () {
                    $record = $this->getRecord();
                    $pdfContent = Blade::render('pdf.usulan-detail', ['record' => $record]);
                    $pdf = Pdf::loadHTML($pdfContent);

                    return response()->streamDownload(
                        fn() => print($pdf->output()),
                        "Usulan-{$record->judul_usulan}.pdf"
                    );
                }),

            Actions\EditAction::make()
                ->visible(fn($record) => in_array($record->status, ['diajukan', 'ditolak_taop'])),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // -- BAGIAN 1: DETAIL UTAMA & RINCIAN BARANG --
                Components\Section::make('Informasi Usulan')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('judul_usulan')->label('Judul Usulan'),
                                Components\TextEntry::make('lab.nama_lab')->label('Diajukan oleh Lab'),
                            ]),

                        // Menggunakan RepeatableEntry untuk menampilkan daftar item
                        Components\RepeatableEntry::make('items')
                            ->label('Rincian Barang')
                            ->schema([
                                Components\TextEntry::make('nama_barang')->label('Nama Barang')->columnSpan(2),
                                Components\TextEntry::make('jumlah')->label('Jml'),
                                Components\TextEntry::make('satuan')->label('Satuan'),
                                Components\TextEntry::make('perkiraan_harga')->label('Harga Est.')
                                    ->money('IDR')
                                    ->alignRight(),
                                Components\TextEntry::make('spesifikasi')->label('Spesifikasi')->columnSpanFull(),
                            ])->columns(5),

                        // Menampilkan Lampiran Usulan
                        Components\ViewEntry::make('lampiran')
                            ->label('Dokumen Pendukung')
                            ->view('filament.infolists.components.lampiran-files')
                            ->visible(fn(Model $record) => !empty($record->lampiran)),
                    ]),

                // -- BAGIAN 2: TIMELINE PERSETUJUAN --
                Components\Section::make('Riwayat Persetujuan')
                    ->schema([
                        Components\Fieldset::make('Tahap 1: Pengajuan Lab')
                            ->schema([
                                Components\Grid::make(2)
                                    ->schema([
                                        Components\TextEntry::make('user.firstname')->label('Diajukan oleh')
                                            ->formatStateUsing(fn(Model $record) => "{$record->user->firstname} {$record->user->lastname}"),
                                        Components\TextEntry::make('tanggal_usulan')->label('Pada Tanggal')->date('d F Y'),
                                    ]),
                            ]),

                        Components\Fieldset::make('Tahap 2: Persetujuan TAOP')
                            ->schema([
                                Components\Grid::make(2)
                                    ->schema([
                                        Components\TextEntry::make('pemeriksa.firstname')->label('Diperiksa oleh')
                                            ->formatStateUsing(fn(Model $record) => $record->pemeriksa ? "{$record->pemeriksa->firstname} {$record->pemeriksa->lastname}" : '-')
                                            ->placeholder('Belum diperiksa'),
                                        Components\TextEntry::make('tanggal_pemeriksaan')->label('Tanggal Pemeriksaan')
                                            ->dateTime('d F Y, H:i')
                                            ->placeholder('Belum diperiksa'),
                                    ]),
                                Components\TextEntry::make('status')
                                    ->label('Keputusan')
                                    ->badge()
                                    ->color(function (string $state, Model $record): string {
                                        if (is_null($record->diperiksa_oleh_id) && $state === 'diajukan') return 'warning';
                                        if (is_null($record->diperiksa_oleh_id)) return 'gray';
                                        return match ($state) {
                                            'ditolak_taop' => 'danger',
                                            default => 'success' // Default disetujui jika sudah ada diperiksa_oleh_id
                                        };
                                    })
                                    ->formatStateUsing(function (string $state, Model $record): string {
                                        if (is_null($record->diperiksa_oleh_id) && $state === 'diajukan') return 'Menunggu Persetujuan TAOP';
                                        if (is_null($record->diperiksa_oleh_id)) return 'Menunggu Persetujuan TAOP';
                                        return match ($state) {
                                            'ditolak_taop' => 'Ditolak untuk Revisi',
                                            default => 'Diteruskan ke Adum' // Default disetujui jika sudah ada diperiksa_oleh_id
                                        };
                                    }),
                                Components\TextEntry::make('catatan_revisi')
                                    ->label('Catatan Revisi')
                                    ->visible(fn($state) => !empty($state))
                                    ->columnSpanFull(),
                            ]),

                        Components\Fieldset::make('Tahap 3: Persetujuan ADUM')
                            ->schema([
                                Components\Grid::make(2)
                                    ->schema([
                                        Components\TextEntry::make('adum.firstname')->label('Diperiksa oleh')
                                            ->formatStateUsing(fn(Model $record) => $record->adum ? "{$record->adum->firstname} {$record->adum->lastname}" : '-')
                                            ->placeholder('Belum diperiksa'),
                                        Components\TextEntry::make('adum_approved_at')->label('Tanggal Pemeriksaan')
                                            ->dateTime('d F Y, H:i')
                                            ->placeholder('Belum diperiksa'),
                                    ]),
                                Components\TextEntry::make('status')
                                    ->label('Keputusan')
                                    ->badge()
                                    ->color(function (string $state, Model $record): string {
                                        // Jika belum ada yang memeriksa
                                        if (is_null($record->adum_user_id)) return 'gray';

                                        // Jika sudah ada yang memeriksa, lihat status
                                        return match ($state) {
                                            'ditolak_adum' => 'danger',
                                            'disetujui_adum', 'ditolak_pimpinan', 'disetujui_pimpinan', 'ditunda', 'direalisasikan', 'selesai' => 'success',
                                            default => 'success' // Default disetujui jika sudah ada adum_user_id
                                        };
                                    })
                                    ->formatStateUsing(function (string $state, Model $record): string {
                                        // Jika belum ada yang memeriksa
                                        if (is_null($record->adum_user_id)) return 'Menunggu Persetujuan ADUM';

                                        // Jika sudah ada yang memeriksa
                                        return match ($state) {
                                            'ditolak_adum' => 'Ditolak',
                                            default => 'Diteruskan ke Pimpinan' // Default disetujui jika sudah ada adum_user_id
                                        };
                                    }),
                                Components\TextEntry::make('catatan_adum')
                                    ->label('Catatan')
                                    ->visible(fn($state) => !empty($state))
                                    ->columnSpanFull(),
                            ]),

                        Components\Fieldset::make('Tahap 4: Persetujuan Pimpinan')
                            ->schema([
                                Components\Grid::make(2)
                                    ->schema([
                                        Components\TextEntry::make('pimpinan.firstname')->label('Diperiksa oleh')
                                            ->formatStateUsing(fn(Model $record) => $record->pimpinan ? "{$record->pimpinan->firstname} {$record->pimpinan->lastname}" : '-')
                                            ->placeholder('Belum diperiksa'),
                                        Components\TextEntry::make('pimpinan_approved_at')->label('Tanggal Pemeriksaan')
                                            ->dateTime('d F Y, H:i')
                                            ->placeholder('Belum diperiksa'),
                                    ]),
                                Components\TextEntry::make('status')
                                    ->label('Keputusan')
                                    ->badge()
                                    ->color(function (string $state, Model $record): string {
                                        // Jika belum ada yang memeriksa
                                        if (is_null($record->pimpinan_user_id)) return 'gray';

                                        // Jika sudah ada yang memeriksa, lihat status
                                        return match ($state) {
                                            'ditolak_pimpinan' => 'danger',
                                            'disetujui_pimpinan', 'ditunda', 'direalisasikan', 'selesai' => 'success',
                                            default => 'success' // Default disetujui jika sudah ada pimpinan_user_id
                                        };
                                    })
                                    ->formatStateUsing(function (string $state, Model $record): string {
                                        // Jika belum ada yang memeriksa
                                        if (is_null($record->pimpinan_user_id)) return 'Menunggu Persetujuan Pimpinan';

                                        // Jika sudah ada yang memeriksa
                                        return match ($state) {
                                            'ditolak_pimpinan' => 'Ditolak',
                                            default => 'Disetujui untuk diproses' // Default disetujui jika sudah ada pimpinan_user_id
                                        };
                                    }),
                                Components\TextEntry::make('catatan_pimpinan')
                                    ->label('Catatan')
                                    ->visible(fn($state) => !empty($state))
                                    ->columnSpanFull(),
                            ]),

                        Components\Fieldset::make('Tahap 5: Proses PPK')
                            ->schema([
                                Components\Grid::make(2)
                                    ->schema([
                                        Components\TextEntry::make('ppk.firstname')->label('Diproses oleh')
                                            ->formatStateUsing(fn(Model $record) => $record->ppk ? "{$record->ppk->firstname} {$record->ppk->lastname}" : '-')
                                            ->placeholder('Belum diproses'),
                                        Components\TextEntry::make('ppk_processed_at')->label('Tanggal Proses')
                                            ->dateTime('d F Y, H:i')
                                            ->placeholder('Belum diproses'),
                                    ]),
                                Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(function (string $state, Model $record): string {
                                        if (is_null($record->ppk_user_id)) return 'gray';
                                        return match ($state) {
                                            'ditunda' => 'warning',
                                            'direalisasikan', 'selesai' => 'success',
                                            default => 'info' // Default sedang diproses jika sudah ada ppk_user_id
                                        };
                                    })
                                    ->formatStateUsing(function (string $state, Model $record): string {
                                        if (is_null($record->ppk_user_id)) return 'Menunggu Proses PPK';
                                        return match ($state) {
                                            'ditunda' => 'Ditunda',
                                            'direalisasikan' => 'Direalisasikan',
                                            'selesai' => 'Selesai (Diserahterimakan)',
                                            default => 'Sedang Diproses' // Default sedang diproses jika sudah ada ppk_user_id
                                        };
                                    }),
                                Components\TextEntry::make('catatan_ppk')
                                    ->label('Catatan')
                                    ->visible(fn($state) => !empty($state))
                                    ->columnSpanFull(),
                                Components\TextEntry::make('ditunda_hingga')
                                    ->label('Ditunda Hingga')
                                    ->date('d F Y')
                                    ->visible(fn($state, Model $record) => !is_null($state) && $record->status === 'ditunda')
                                    ->color('warning'),
                            ])
                            ->visible(fn(Model $record) => !is_null($record->ppk_user_id) || in_array($record->status, ['disetujui_pimpinan', 'ditunda', 'direalisasikan', 'selesai'])),

                        Components\Fieldset::make('Tahap 6: Serah Terima Barang')
                            ->schema([
                                Components\Grid::make(2)
                                    ->schema([
                                        Components\TextEntry::make('ppk.firstname')->label('Diserahkan oleh')
                                            ->formatStateUsing(fn(Model $record) => $record->ppk ? "{$record->ppk->firstname} {$record->ppk->lastname}" : '-'),
                                        Components\TextEntry::make('serah_terima_at')->label('Tanggal Serah Terima')
                                            ->dateTime('d F Y, H:i'),
                                    ]),
                                Components\TextEntry::make('catatan_serah_terima')
                                    ->label('Catatan')
                                    ->visible(fn($state) => !empty($state))
                                    ->columnSpanFull(),

                                // Bukti serah terima
                                Components\ViewEntry::make('bukti_serah_terima')
                                    ->label('Bukti Serah Terima')
                                    ->view('filament.infolists.components.lampiran-files')
                                    ->visible(function (Model $record) {
                                        // Debug: cek raw data
                                        if (empty($record->bukti_serah_terima)) return false;

                                        // Jika string JSON, decode dulu
                                        if (is_string($record->bukti_serah_terima)) {
                                            $decoded = json_decode($record->bukti_serah_terima, true);
                                            return !empty($decoded);
                                        }

                                        // Jika array langsung
                                        return is_array($record->bukti_serah_terima) && !empty($record->bukti_serah_terima);
                                    }),
                            ])
                            ->visible(fn(Model $record) => $record->status === 'selesai'),
                    ])
                    ->collapsible(),
            ]);
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsulanResource\Pages;
use App\Models\Usulan;
use App\Models\Lab;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UsulanResource extends Resource
{
    protected static ?string $model = Usulan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Master Data';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user->hasAnyRole(['Super Admin', 'Admin', 'super_admin', 'admin', 'TAOP', 'ADUM', 'PIMPINAN', 'PPK'])) {
            return $query;
        }

        $roleToLabId = Lab::all()->pluck('id', 'nama_lab')->toArray();
        $accessibleLabIds = [];
        foreach ($user->getRoleNames() as $roleName) {
            if (array_key_exists($roleName, $roleToLabId)) {
                $accessibleLabIds[] = $roleToLabId[$roleName];
            }
        }

        if (!empty($accessibleLabIds)) {
            return $query->whereIn('lab_id', $accessibleLabIds);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canCreate(): bool
    {
        return !Auth::user()->hasAnyRole(['Super Admin', 'Admin', 'super_admin', 'admin', 'TAOP', 'ADUM', 'PIMPINAN', 'PPK']);
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $userLabId = null;
        $isLabUser = false;

        if (!$user->hasAnyRole(['Super Admin', 'Admin', 'super_admin', 'admin', 'TAOP', 'ADUM', 'PIMPINAN', 'PPK'])) {
            $roleToLabId = Lab::all()->pluck('id', 'nama_lab')->toArray();
            foreach ($user->getRoleNames() as $roleName) {
                if (array_key_exists($roleName, $roleToLabId)) {
                    $userLabId = $roleToLabId[$roleName];
                    $isLabUser = true;
                    break;
                }
            }
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Umum Usulan')
                    ->schema([
                        Forms\Components\TextInput::make('judul_usulan')
                            ->label('Judul / Perihal Usulan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('lab_id')
                            ->label('Laboratorium')
                            ->options(Lab::all()->pluck('nama_lab', 'id'))
                            ->required()
                            ->searchable()
                            ->default($userLabId)
                            ->disabled($isLabUser)
                            ->dehydrated(),
                        Forms\Components\FileUpload::make('lampiran')
                            ->label('Dokumen Pendukungg (Opsional)')
                            ->directory('dokumen-pendukung-usulan')
                            ->openable()
                            ->downloadable()
                            ->previewable(true)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Rincian Barang yang Diusulkan')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('nama_barang')->required()->columnSpan(2),
                                Forms\Components\TextInput::make('jumlah')->required()->numeric()->default(1),
                                Forms\Components\TextInput::make('satuan')->required()->default('unit'),
                                Forms\Components\TextInput::make('perkiraan_harga')->numeric()->prefix('Rp')->nullable(),
                                Forms\Components\Textarea::make('spesifikasi')->columnSpanFull(),
                            ])
                            ->columns(5)
                            ->addActionLabel('Tambah Barang')
                            ->defaultItems(1)
                            ->collapsible()
                            ->cloneable(),
                    ]),

                Forms\Components\Hidden::make('user_id')->default(Auth::id()),
                Forms\Components\Hidden::make('tanggal_usulan')->default(now()),
                Forms\Components\Hidden::make('status')->default('diajukan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul_usulan')
                    ->searchable()
                    ->description(fn(Usulan $record): string => "Lab: {$record->lab->nama_lab}"),

                IconColumn::make('diperiksa_oleh_id')
                    ->label('TAOP')
                    ->icon(fn(Model $record): string => match (true) {
                        !is_null($record->tanggal_pemeriksaan) => 'heroicon-o-check-circle',
                        !is_null($record->diperiksa_oleh_id) => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->color(fn(Model $record): string => match (true) {
                        !is_null($record->tanggal_pemeriksaan) => 'success',
                        !is_null($record->diperiksa_oleh_id) => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('adum_user_id')
                    ->label('Adum')
                    ->icon(fn(Model $record): string => match (true) {
                        !is_null($record->adum_approved_at) => 'heroicon-o-check-circle',
                        !is_null($record->adum_user_id) => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->color(fn(Model $record): string => match (true) {
                        !is_null($record->adum_approved_at) => 'success',
                        !is_null($record->adum_user_id) => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('pimpinan_user_id')
                    ->label('Pimpinan')
                    ->icon(fn(Model $record): string => match (true) {
                        !is_null($record->pimpinan_approved_at) => 'heroicon-o-check-circle',
                        !is_null($record->pimpinan_user_id) => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->color(fn(Model $record): string => match (true) {
                        !is_null($record->pimpinan_approved_at) => 'success',
                        !is_null($record->pimpinan_user_id) => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('ppk_user_id')
                    ->label('PPK')
                    ->icon(fn(Model $record): string => match (true) {
                        $record->status === 'selesai' => 'heroicon-o-hand-thumb-up',
                        $record->status === 'direalisasikan' => 'heroicon-o-check-circle',
                        $record->status === 'ditunda' => 'heroicon-o-pause-circle',
                        !is_null($record->ppk_user_id) => 'heroicon-o-check-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->color(fn(Model $record): string => match (true) {
                        $record->status === 'selesai' => 'success',
                        $record->status === 'direalisasikan' => 'success',
                        $record->status === 'ditunda' => 'warning',
                        !is_null($record->ppk_user_id) => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'diajukan' => 'gray',
                        'ditolak_taop' => 'danger',
                        'diteruskan_adum' => 'info',
                        'ditolak_adum' => 'danger',
                        'diteruskan_pimpinan' => 'warning',
                        'ditolak_pimpinan' => 'danger',
                        'disetujui' => 'primary',
                        'ditunda' => 'warning',
                        'direalisasikan' => 'success',
                        'selesai' => 'success',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_usulan')
                    ->date('d M Y')
                    ->sortable()
                    ->label('Tgl. Usulan'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lab_id')
                    ->label('Filter by Lab')
                    ->relationship('lab', 'nama_lab'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('setujui_adum')
                        ->label('Setujui (ADUM)')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(Model $record): bool => Auth::user()->hasAnyRole(['ADUM', 'Super Admin']) && $record->status === 'diteruskan_adum')
                        ->action(function (Model $record) {
                            $record->update([
                                'status' => 'diteruskan_pimpinan',
                                'adum_user_id' => Auth::id(),
                                'adum_approved_at' => now(),
                            ]);
                        }),
                    Tables\Actions\Action::make('tolak_adum')
                        ->label('Tolak (ADUM)')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->visible(fn(Model $record): bool => Auth::user()->hasAnyRole(['ADUM', 'Super Admin']) && $record->status === 'diteruskan_adum')
                        ->form([Forms\Components\Textarea::make('catatan_adum')->label('Catatan Penolakan')->required()])
                        ->action(function (Model $record, array $data) {
                            $record->update([
                                'status' => 'ditolak_adum',
                                'catatan_adum' => $data['catatan_adum'],
                                'adum_user_id' => Auth::id(),
                            ]);
                        }),

                    Tables\Actions\Action::make('setujui_pimpinan')
                        ->label('Setujui (PIMPINAN)')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(Model $record): bool => Auth::user()->hasAnyRole(['PIMPINAN', 'Super Admin']) && $record->status === 'diteruskan_pimpinan')
                        ->action(function (Model $record) {
                            $record->update([
                                'status' => 'disetujui',
                                'pimpinan_user_id' => Auth::id(),
                                'pimpinan_approved_at' => now(),
                            ]);
                        }),
                    Tables\Actions\Action::make('tolak_pimpinan')
                        ->label('Tolak (PIMPINAN)')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn(Model $record): bool => Auth::user()->hasAnyRole(['PIMPINAN', 'Super Admin']) && $record->status === 'diteruskan_pimpinan')
                        ->form([Forms\Components\Textarea::make('catatan_pimpinan')->label('Alasan Penolakan Final')->required()])
                        ->action(function (Model $record, array $data) {
                            $record->update([
                                'status' => 'ditolak_pimpinan',
                                'catatan_pimpinan' => $data['catatan_pimpinan'],
                                'pimpinan_user_id' => Auth::id(),
                            ]);
                        }),

                    Tables\Actions\Action::make('realisasi')
                        ->label('Realisasikan')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(Model $record): bool => Auth::user()->hasAnyRole(['PPK', 'Super Admin']) && $record->status === 'disetujui')
                        ->action(function (Model $record) {
                            $record->update([
                                'status' => 'direalisasikan',
                                'ppk_user_id' => Auth::id(),
                                'ppk_processed_at' => now(),
                            ]);
                        }),
                    Tables\Actions\Action::make('tunda')
                        ->label('Tunda 1 Tahun')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->visible(fn(Model $record): bool => Auth::user()->hasAnyRole(['PPK', 'Super Admin']) && $record->status === 'disetujui')
                        ->form([
                            Forms\Components\Textarea::make('catatan_ppk')->label('Catatan Penundaan (Opsional)'),
                        ])
                        ->action(function (Model $record, array $data) {
                            $record->update([
                                'status' => 'ditunda',
                                'catatan_ppk' => $data['catatan_ppk'],
                                'ditunda_hingga' => Carbon::parse($record->tanggal_usulan)->addYear(),
                                'ppk_user_id' => Auth::id(),
                                'ppk_processed_at' => now(),
                            ]);
                        }),

                    Tables\Actions\Action::make('serah_terima')
                        ->label('Konfirmasi Serah Terima')
                        ->icon('heroicon-o-archive-box-arrow-down')
                        ->color('success')
                        ->visible(fn(Model $record): bool => Auth::user()->hasAnyRole(['PPK', 'Super Admin']) && $record->status === 'direalisasikan')
                        ->form([
                            Forms\Components\Checkbox::make('konfirmasi')
                                ->label('Saya mengonfirmasi bahwa barang yang diterima telah sesuai dengan spesifikasi usulan.')
                                ->accepted()
                                ->required(),
                            Forms\Components\FileUpload::make('bukti_serah_terima')
                                ->label('Bukti Serah Terima (BAST/Foto)')
                                ->directory('bukti-serah-terima')
                                ->required(),
                            Forms\Components\Textarea::make('catatan_serah_terima')
                                ->label('Catatan (Opsional)'),
                        ])
                        ->action(function (Model $record, array $data): void {
                            $record->status = 'selesai';
                            $record->bukti_serah_terima = $data['bukti_serah_terima'];
                            $record->catatan_serah_terima = $data['catatan_serah_terima'];
                            $record->serah_terima_at = now();
                            if (is_null($record->ppk_user_id)) {
                                $record->ppk_user_id = Auth::id();
                            }
                            $record->save();
                        }),

                    Tables\Actions\Action::make('teruskan')
                        ->label('Teruskan ke Adum')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn(Model $record): bool => Auth::user()->hasAnyRole(['TAOP', 'Super Admin']) && $record->status === 'diajukan')
                        ->action(function (Model $record): void {
                            $record->update([
                                'status' => 'diteruskan_adum',
                                'diperiksa_oleh_id' => Auth::id(),
                                'tanggal_pemeriksaan' => now(),
                            ]);
                        }),

                    Tables\Actions\Action::make('tolak')
                        ->label('Tolak (TAOP)')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn(Model $record): bool => Auth::user()->hasAnyRole(['TAOP', 'Super Admin']) && $record->status === 'diajukan')
                        ->form([
                            Forms\Components\Textarea::make('catatan_revisi')
                                ->label('Alasan Penolakan / Catatan Revisi')
                                ->required(),
                        ])
                        ->action(function (Model $record, array $data): void {
                            $record->update([
                                'status' => 'ditolak_taop',
                                'catatan_revisi' => $data['catatan_revisi'],
                                'diperiksa_oleh_id' => Auth::id(),
                            ]);
                        }),

                    Tables\Actions\EditAction::make()
                        ->visible(fn(Usulan $record) => in_array($record->status, ['diajukan', 'ditolak_taop']))
                        ->after(function (Model $record) {
                            if ($record->getOriginal('status') === 'ditolak_taop') {
                                $record->status = 'diajukan';
                                $record->catatan_revisi = null;
                                $record->diperiksa_oleh_id = null;
                                $record->tanggal_pemeriksaan = null;
                                $record->save();
                            }
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->visible(fn(Usulan $record) => in_array($record->status, ['diajukan', 'ditolak_taop'])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsulans::route('/'),
            'create' => Pages\CreateUsulan::route('/create'),
            'edit' => Pages\EditUsulan::route('/{record}/edit'),
            'view' => Pages\ViewUsulan::route('/{record}'),
        ];
    }
}

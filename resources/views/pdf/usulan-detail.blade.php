<!DOCTYPE html>
<html>

<head>
    <title>Detail Usulan - {{ $record->judul_usulan }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header,
        .footer {
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .header p {
            margin: 5px 0;
            font-size: 12px;
        }

        .content {
            margin-top: 15px;
        }

        .section {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
        }

        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 10px;
            color: #111;
        }

        .info-table {
            width: 100%;
        }

        .info-table th,
        .info-table td {
            padding: 5px 0;
            vertical-align: top;
        }

        .info-table th {
            text-align: left;
            width: 150px;
            font-weight: normal;
            color: #555;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .item-table th,
        .item-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        .item-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .decision {
            font-weight: bold;
        }

        .decision.success {
            color: #16a34a;
        }

        .decision.danger {
            color: #dc2626;
        }

        .decision.warning {
            color: #f59e0b;
        }

        .note {
            font-style: italic;
            color: #555;
            padding-left: 15px;
            border-left: 3px solid #eee;
        }

        hr {
            border: none;
            border-top: 1px solid #eee;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>DETAIL DOKUMEN USULAN</h1>
            <p><strong>Status Akhir:</strong> <span class="decision {{ $record->status === 'ditolak_pimpinan' || $record->status === 'ditolak_adum' || $record->status === 'ditolak_taop' ? 'danger' : 'success' }}">{{ Str::headline($record->status) }}</span></p>
        </div>

        <div class="content">
            <div class="section">
                <div class="section-title">Informasi Umum</div>
                <table class="info-table">
                    <tr>
                        <th>Judul Usulan</th>
                        <td>: {{ $record->judul_usulan }}</td>
                    </tr>
                    <tr>
                        <th>Diajukan oleh Lab</th>
                        <td>: {{ $record->lab->nama_lab }}</td>
                    </tr>
                    <tr>
                        <th>Pada Tanggal</th>
                        <td>: {{ $record->tanggal_usulan->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <th>User Pengusul</th>
                        <td>: {{ $record->user->firstname }} {{ $record->user->lastname }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Rincian Barang yang Diusulkan</div>
                <table class="item-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Spesifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($record->items as $index => $item)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td>{{ $item->nama_barang }}</td>
                            <td style="text-align: center;">{{ $item->jumlah }}</td>
                            <td>{{ $item->satuan }}</td>
                            <td>{{ $item->spesifikasi ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Riwayat Persetujuan & Proses</div>

                {{-- TAHAP TAOP --}}
                @if($record->diperiksa_oleh_id)
                <table class="info-table">
                    <tr>
                        <th width="150px">Persetujuan TAOP</th>
                        <td>:
                            <span class="decision {{ $record->status === 'ditolak_taop' ? 'danger' : 'success' }}">
                                {{ $record->status === 'ditolak_taop' ? 'Ditolak untuk Revisi' : 'Diteruskan' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Oleh</th>
                        <td>: {{ $record->pemeriksa->firstname }} {{ $record->pemeriksa->lastname }}</td>
                    </tr>
                    @if($record->catatan_revisi)
                    <tr>
                        <th>Catatan</th>
                        <td>: <span class="note">{{ $record->catatan_revisi }}</span></td>
                    </tr>
                    @endif
                </table>
                <hr>
                @endif

                {{-- TAHAP ADUM --}}
                @if($record->adum_user_id)
                <table class="info-table">
                    <tr>
                        <th width="150px">Persetujuan Adum</th>
                        <td>:
                            <span class="decision {{ $record->status === 'ditolak_adum' ? 'danger' : 'success' }}">
                                {{ $record->status === 'ditolak_adum' ? 'Ditolak' : 'Diteruskan' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Oleh</th>
                        <td>: {{ $record->adum->firstname }} {{ $record->adum->lastname }}</td>
                    </tr>
                    @if($record->catatan_adum)
                    <tr>
                        <th>Catatan</th>
                        <td>: <span class="note">{{ $record->catatan_adum }}</span></td>
                    </tr>
                    @endif
                </table>
                <hr>
                @endif

                {{-- TAHAP PIMPINAN --}}
                @if($record->pimpinan_user_id)
                <table class="info-table">
                    <tr>
                        <th width="150px">Persetujuan Pimpinan</th>
                        <td>:
                            <span class="decision {{ $record->status === 'ditolak_pimpinan' ? 'danger' : 'success' }}">
                                {{ $record->status === 'ditolak_pimpinan' ? 'Ditolak' : 'Disetujui' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Oleh</th>
                        <td>: {{ $record->pimpinan->firstname }} {{ $record->pimpinan->lastname }}</td>
                    </tr>
                    @if($record->catatan_pimpinan)
                    <tr>
                        <th>Catatan</th>
                        <td>: <span class="note">{{ $record->catatan_pimpinan }}</span></td>
                    </tr>
                    @endif
                </table>
                <hr>
                @endif

                {{-- TAHAP PPK --}}
                @if($record->ppk_user_id)
                <table class="info-table">
                    <tr>
                        <th width="150px">Proses PPK</th>
                        <td>:
                            <span class="decision {{ $record->status === 'ditunda' ? 'warning' : 'success' }}">
                                {{ $record->status === 'ditunda' ? 'Ditunda' : ($record->status === 'selesai' ? 'Selesai' : 'Direalisasikan') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Oleh</th>
                        <td>: {{ $record->ppk->firstname }} {{ $record->ppk->lastname }}</td>
                    </tr>
                    @if($record->status === 'ditunda')
                    <tr>
                        <th>Ditunda Hingga</th>
                        <td>: {{ $record->ditunda_hingga->format('d F Y') }}</td>
                    </tr>
                    @endif
                    @if($record->catatan_ppk)
                    <tr>
                        <th>Catatan</th>
                        <td>: <span class="note">{{ $record->catatan_ppk }}</span></td>
                    </tr>
                    @endif
                </table>
                @endif

            </div>
        </div>

        <div class="footer">
            <p>Dokumen ini dicetak dari Sistem Aplikasi Usulan pada {{ now()->format('d F Y, H:i') }}</p>
        </div>
    </div>
</body>

</html>
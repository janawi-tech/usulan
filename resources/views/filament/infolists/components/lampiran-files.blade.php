{{--
  File: resources/views/filament/infolists/components/lampiran-files.blade.php
  Deskripsi: Versi final yang menangani format data JSON (string tunggal atau array) 
             dan menggunakan method Storage Laravel yang benar.
--}}

@php
// --- LOGIKA PARSING DATA (SUDAH BENAR) ---
$files = [];
$rawData = $getState();

// Logika ini dirancang untuk menangani semua kemungkinan format:
// 1. Array PHP: ['file1.jpg']
// 2. String JSON array: '["file1.jpg", "file2.pdf"]'
// 3. String JSON string tunggal: '"file1.jpg"' <-- Ini kasus Anda
    // 4. String path tunggal (non-JSON): 'file1.jpg'
    if (is_string($rawData) && !empty(trim($rawData))) {
    $decodedData=json_decode($rawData, true);
    if (is_array($decodedData)) {
    $files=$decodedData;
    } elseif (is_string($decodedData)) {
    $files=[$decodedData];
    } else {
    $files=[$rawData];
    }
    } elseif (is_array($rawData)) {
    $files=$rawData;
    }

    $files=array_filter($files, fn($file)=> !empty($file));
    @endphp

    <div class="space-y-3">
        @forelse($files as $file)
        @php
        // --- LOGIKA PEMERIKSAAN FILE MENGGUNAKAN STORAGE (SUDAH BENAR) ---
        $disk = 'public'; // Pastikan disk 'public' digunakan
        $normalizedFile = str_replace('\\', '/', $file);
        $fileExists = Illuminate\Support\Facades\Storage::disk($disk)->exists($normalizedFile);
        $isImage = false;

        if ($fileExists) {
        try {
        $mimeType = Illuminate\Support\Facades\Storage::disk($disk)->mimeType($normalizedFile);
        $isImage = str_starts_with($mimeType, 'image/');
        } catch (Exception $e) {
        $isImage = false;
        }
        }
        @endphp

        {{--
            ===============================================================
            DEBUGGING SECTION (Anda bisa hapus komentar di bawah ini untuk melihat path)
            ===============================================================
        --}}
        {{--
        <div class="p-2 bg-yellow-100 text-yellow-800 rounded-md text-xs">
            <p><strong>Raw Path from DB:</strong> {{ $file }}</p>
        <p><strong>Normalized Path:</strong> {{ $normalizedFile }}</p>
        <p><strong>Disk Checked:</strong> {{ $disk }}</p>
        <p><strong>Full Physical Path Checked:</strong> {{ Illuminate\Support\Facades\Storage::disk($disk)->path($normalizedFile) }}</p>
        <p><strong>Exists?:</strong> {{ $fileExists ? 'YES' : 'NO' }}</p>
    </div>
    --}}
    {{-- =============================================================== --}}


    <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border">
        {{-- Bagian untuk menampilkan Gambar --}}
        @if($fileExists && $isImage)
        <div class="flex-shrink-0">
            <img src="{{ Illuminate\Support\Facades\Storage::url($normalizedFile) }}"
                alt="Lampiran"
                class="w-24 h-24 object-cover rounded-lg cursor-pointer shadow-sm hover:shadow-md transition-shadow"
                onclick="window.open('{{ Illuminate\Support\Facades\Storage::url($normalizedFile) }}', '_blank')">
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                {{ basename($normalizedFile) }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Gambar • {{ number_format(Illuminate\Support\Facades\Storage::disk($disk)->size($normalizedFile) / 1024, 1) }} KB
            </p>
            <a href="{{ Illuminate\Support\Facades\Storage::url($normalizedFile) }}"
                target="_blank"
                class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mt-1">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                Lihat ukuran penuh
            </a>
        </div>
        {{-- Bagian untuk menampilkan Dokumen (bukan gambar) atau Pesan Error --}}
        @else
        <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                {{ basename($normalizedFile) }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                @if($fileExists)
                Dokumen • {{ number_format(Illuminate\Support\Facades\Storage::disk($disk)->size($normalizedFile) / 1024, 1) }} KB
                @else
                <span class="text-red-500">File tidak ditemukan: {{ $normalizedFile }}</span>
                @endif
            </p>
            @if($fileExists)
            <a href="{{ Illuminate\Support\Facades\Storage::url($normalizedFile) }}"
                target="_blank"
                class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mt-1">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Download
            </a>
            @endif
        </div>
        @endif
    </div>
    @empty
    <div class="text-center py-6">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 italic">Tidak ada dokumen pendukung</p>
    </div>
    @endforelse
    </div>
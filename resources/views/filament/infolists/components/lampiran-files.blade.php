{{-- resources/views/filament/infolists/components/lampiran-files.blade.php --}}

@php
// Decode JSON string jika diperlukan
$files = [];
$rawData = $getState();

if (is_string($rawData)) {
// Coba decode JSON dulu
$decoded = json_decode($rawData, true);
if ($decoded && is_array($decoded)) {
$files = $decoded;
} else {
// Jika bukan JSON, treat sebagai single file path
$files = [$rawData];
}
} elseif (is_array($rawData)) {
$files = $rawData;
}

// Filter file yang tidak kosong
$files = array_filter($files, fn($file) => !empty($file));
@endphp

<div class="space-y-3">
    @if(!empty($files))
    @foreach($files as $file)
    @php
    // Normalize path separator untuk cross-platform compatibility
    $normalizedFile = str_replace('\\', '/', $file);
    $filePath = storage_path('app/public/' . $normalizedFile);
    $fileExists = file_exists($filePath);
    $isImage = false;

    if ($fileExists) {
    try {
    $mimeType = mime_content_type($filePath);
    $isImage = str_starts_with($mimeType, 'image/');
    } catch (Exception $e) {
    $isImage = false;
    }
    }
    @endphp

    <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border">
        @if($fileExists && $isImage)
        <div class="flex-shrink-0">
            <img src="{{ Storage::url($normalizedFile) }}"
                alt="Lampiran"
                class="w-24 h-24 object-cover rounded-lg cursor-pointer shadow-sm hover:shadow-md transition-shadow"
                onclick="window.open('{{ Storage::url($normalizedFile) }}', '_blank')">
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                {{ basename($normalizedFile) }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Gambar • {{ number_format(filesize($filePath) / 1024, 1) }} KB
            </p>
            <a href="{{ Storage::url($normalizedFile) }}"
                target="_blank"
                class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mt-1">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                Lihat ukuran penuh
            </a>
        </div>
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
                Dokumen • {{ number_format(filesize($filePath) / 1024, 1) }} KB
                @else
                <span class="text-red-500">File tidak ditemukan: {{ $normalizedFile }}</span>
                @endif
            </p>
            @if($fileExists)
            <a href="{{ Storage::url($normalizedFile) }}"
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
    @endforeach
    @else
    <div class="text-center py-6">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 italic">Tidak ada dokumen pendukung</p>
    </div>
    @endif
</div>
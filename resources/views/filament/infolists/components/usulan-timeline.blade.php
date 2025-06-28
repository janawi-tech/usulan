@php
$status = $getState()['status'];
$createdAt = $getState()['created_at'];
$updatedAt = $getState()['updated_at'];
$catatanTaop = $getState()['catatan_taop'];

$steps = [
'draft' => ['label' => 'Draft', 'color' => 'gray', 'icon' => 'document-text'],
'diajukan' => ['label' => 'Diajukan ke TAOP', 'color' => 'blue', 'icon' => 'paper-airplane'],
'dikembalikan' => ['label' => 'Dikembalikan', 'color' => 'yellow', 'icon' => 'arrow-uturn-left'],
'diterima_taop' => ['label' => 'Diterima TAOP', 'color' => 'cyan', 'icon' => 'check-circle'],
'disetujui' => ['label' => 'Disetujui', 'color' => 'green', 'icon' => 'check-badge'],
'ditolak' => ['label' => 'Ditolak', 'color' => 'red', 'icon' => 'x-circle'],
];

$currentStep = $steps[$status] ?? $steps['draft'];
@endphp

<div class="space-y-4">
    <div class="flex items-center space-x-4">
        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-{{ $currentStep['color'] }}-100 text-{{ $currentStep['color'] }}-600">
            <x-heroicon-o-{{ $currentStep['icon'] }} class="w-6 h-6" />
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $currentStep['label'] }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Status saat ini: {{ $status }}
            </p>
        </div>
    </div>

    @if($status !== 'draft')
    <div class="ml-6 pl-6 border-l-2 border-gray-200 dark:border-gray-700 space-y-3">
        <div class="text-sm">
            <span class="font-medium text-gray-700 dark:text-gray-300">Dibuat:</span>
            <span class="text-gray-600 dark:text-gray-400">{{ $createdAt->format('d F Y, H:i') }}</span>
        </div>

        @if($updatedAt && $updatedAt != $createdAt)
        <div class="text-sm">
            <span class="font-medium text-gray-700 dark:text-gray-300">Terakhir diupdate:</span>
            <span class="text-gray-600 dark:text-gray-400">{{ $updatedAt->format('d F Y, H:i') }}</span>
        </div>
        @endif

        @if($catatanTaop)
        <div class="text-sm">
            <span class="font-medium text-gray-700 dark:text-gray-300">Catatan TAOP:</span>
            <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-gray-600 dark:text-gray-400">{{ $catatanTaop }}</p>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Progress Steps Visual --}}
    <div class="mt-6">
        <div class="flex items-center justify-between">
            @php
            $allSteps = ['draft', 'diajukan', 'diterima_taop', 'disetujui'];
            $currentIndex = array_search($status, $allSteps);
            if ($status === 'dikembalikan') $currentIndex = 1; // Same level as diajukan
            if ($status === 'ditolak') $currentIndex = 3; // Same level as disetujui
            @endphp

            @foreach($allSteps as $index => $stepKey)
            @php
            $stepData = $steps[$stepKey];
            $isActive = $index <= $currentIndex;
                $isCurrent=$stepKey===$status ||
                ($stepKey==='diajukan' && $status==='dikembalikan' ) ||
                ($stepKey==='disetujui' && $status==='ditolak' );
                @endphp

                <div class="flex flex-col items-center flex-1">
                <div class="flex items-center justify-center w-8 h-8 rounded-full 
                        {{ $isActive ? 'bg-' . $stepData['color'] . '-500 text-white' : 'bg-gray-200 text-gray-400' }}
                        {{ $isCurrent ? 'ring-4 ring-' . $stepData['color'] . '-200' : '' }}">
                    <x-heroicon-o-{{ $stepData['icon'] }} class="w-4 h-4" />
                </div>
                <span class="mt-2 text-xs text-center text-gray-600 dark:text-gray-400 max-w-20">
                    {{ $stepData['label'] }}
                </span>
        </div>

        @if(!$loop->last)
        <div class="flex-1 h-1 mx-2 {{ $index < $currentIndex ? 'bg-green-500' : 'bg-gray-200' }} rounded"></div>
        @endif
        @endforeach
    </div>
</div>
</div>
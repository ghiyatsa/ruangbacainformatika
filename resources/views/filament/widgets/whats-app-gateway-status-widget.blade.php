@php
    $data = $this->getViewData();
    $pollingInterval = $this->pollingInterval ?? null;

    $colorMap = [
        'gray'    => ['bg' => 'bg-gray-50 dark:bg-gray-900/50',    'border' => 'border-gray-200 dark:border-gray-700',    'text' => 'text-gray-600 dark:text-gray-400',    'badge' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'],
        'success' => ['bg' => 'bg-green-50 dark:bg-green-950/40',  'border' => 'border-green-200 dark:border-green-800',  'text' => 'text-green-700 dark:text-green-400',  'badge' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'],
        'danger'  => ['bg' => 'bg-red-50 dark:bg-red-950/40',      'border' => 'border-red-200 dark:border-red-800',      'text' => 'text-red-700 dark:text-red-400',      'badge' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'],
        'warning' => ['bg' => 'bg-amber-50 dark:bg-amber-950/40',  'border' => 'border-amber-200 dark:border-amber-800',  'text' => 'text-amber-700 dark:text-amber-400',  'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300'],
    ];

    $c = $colorMap[$data['connectionColor']] ?? $colorMap['gray'];
@endphp

<x-filament-widgets::widget
    @if ($pollingInterval)
        wire:poll.{{ $pollingInterval }}
    @endif
    class="fi-wi-whatsapp-gateway-status"
>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

        {{-- Kiri: Status koneksi (span full height) --}}
        <div @class([
            'flex flex-col justify-between gap-4 rounded-xl border p-5',
            $c['bg'],
            $c['border'],
        ])
            @if ($data['connectionTitle'])
                title="{{ $data['connectionTitle'] }}"
            @endif
        >
            {{-- Icon + badge --}}
            <div class="flex items-start justify-between gap-3">
                <div @class(['flex h-10 w-10 shrink-0 items-center justify-center rounded-lg', $c['badge']])>
                    <x-filament::icon
                        :icon="$data['connectionIcon']"
                        class="h-5 w-5"
                    />
                </div>

                <span @class(['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', $c['badge']])>
                    {{ $data['connectionLabel'] }}
                </span>
            </div>

            {{-- Label & deskripsi --}}
            <div class="space-y-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    WhatsApp Gateway
                </p>
                <p @class(['text-2xl font-semibold', $c['text']])>
                    {{ $data['connectionLabel'] }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $data['connectionDescription'] }}
                </p>
            </div>

            {{-- Footer: polling hint --}}
            <p class="text-xs text-gray-400 dark:text-gray-600">
                Diperbarui setiap 60 detik
            </p>
        </div>

        {{-- Kanan: 2 stat vertikal --}}
        <div class="flex flex-col gap-4">

            {{-- Pesan Terkirim --}}
            <a
                href="{{ $data['logsUrl'] }}"
                class="flex flex-1 flex-col justify-between gap-3 rounded-xl border border-gray-200 bg-white p-5 transition hover:border-green-300 hover:bg-green-50/50 dark:border-gray-700 dark:bg-gray-900/50 dark:hover:border-green-800 dark:hover:bg-green-950/30"
            >
                <div class="flex items-center justify-between gap-2">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Terkirim Hari Ini
                    </p>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                        <x-filament::icon icon="heroicon-o-paper-airplane" class="h-4 w-4" />
                    </div>
                </div>

                <div class="flex items-end justify-between gap-2">
                    <span class="text-3xl font-semibold tabular-nums text-gray-900 dark:text-white">
                        {{ number_format($data['sentCount']) }}
                    </span>
                    @if ($data['sentCount'] > 0)
                        <span class="mb-1 text-xs text-green-600 dark:text-green-400">Berhasil dikirim</span>
                    @else
                        <span class="mb-1 text-xs text-gray-400">Belum ada</span>
                    @endif
                </div>
            </a>

            {{-- Pesan Gagal --}}
            <a
                href="{{ $data['logsUrl'] }}"
                @class([
                    'flex flex-1 flex-col justify-between gap-3 rounded-xl border p-5 transition',
                    'border-red-200 bg-red-50 hover:border-red-300 hover:bg-red-50 dark:border-red-800 dark:bg-red-950/30 dark:hover:border-red-700' => $data['failedCount'] > 0,
                    'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-700 dark:bg-gray-900/50 dark:hover:border-gray-600' => $data['failedCount'] === 0,
                ])
            >
                <div class="flex items-center justify-between gap-2">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Gagal Hari Ini
                    </p>
                    <div @class([
                        'flex h-8 w-8 items-center justify-center rounded-lg',
                        'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' => $data['failedCount'] > 0,
                        'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' => $data['failedCount'] === 0,
                    ])>
                        <x-filament::icon icon="heroicon-o-exclamation-circle" class="h-4 w-4" />
                    </div>
                </div>

                <div class="flex items-end justify-between gap-2">
                    <span @class([
                        'text-3xl font-semibold tabular-nums',
                        'text-red-700 dark:text-red-400' => $data['failedCount'] > 0,
                        'text-gray-900 dark:text-white' => $data['failedCount'] === 0,
                    ])>
                        {{ number_format($data['failedCount']) }}
                    </span>
                    @if ($data['failedCount'] > 0)
                        <span class="mb-1 text-xs text-red-600 dark:text-red-400">Perlu pengecekan</span>
                    @else
                        <span class="mb-1 text-xs text-green-600 dark:text-green-400">Tidak ada kegagalan</span>
                    @endif
                </div>
            </a>

        </div>
    </div>
</x-filament-widgets::widget>

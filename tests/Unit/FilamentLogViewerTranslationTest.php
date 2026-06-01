<?php

it('uses readable Indonesian labels for the filament log viewer package', function () {
    app()->setLocale('id');

    expect(__('filament-log-viewer::log.navigation.heading'))->toBe('Tabel Log')
        ->and(__('filament-log-viewer::log.navigation.label'))->toBe('Log Sistem')
        ->and(__('filament-log-viewer::log.table.actions.refresh.label'))->toBe('Muat Ulang')
        ->and(__('filament-log-viewer::log.levels.all'))->toBe('Semua Log');
});

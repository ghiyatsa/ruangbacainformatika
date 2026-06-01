<?php

declare(strict_types=1);

return [
    'placeholder' => 'Tidak tersedia',
    'navigation' => [
        'title' => 'Penampil Log',
        'heading' => 'Tabel Log',
        'subheading' => '',
        'group' => 'Sistem',
        'label' => 'Log Sistem',
    ],
    'table' => [
        'model_label' => 'log',
        'plural_model_label' => 'log',
        'columns' => [
            'log_level' => 'Level Log',
            'env' => 'Lingkungan',
            'file' => 'Nama File',
            'message' => 'Ringkasan',
            'date' => 'Waktu Kejadian',
        ],
        'filters' => [
            'env' => [
                'label' => 'Lingkungan',
                'indicator' => 'Difilter berdasarkan lingkungan',
            ],
            'file' => [
                'label' => 'File',
                'indicator' => 'Difilter berdasarkan file',
            ],
            'date' => [
                'label' => 'Tanggal',
                'indicator' => 'Difilter berdasarkan tanggal',
                'from' => 'Dari',
                'until' => 'Sampai',
            ],
            'date_range' => [
                'label' => 'Rentang Tanggal',
                'indicator' => 'Difilter berdasarkan rentang tanggal',
            ],
            'indicators' => [
                'logs_from_to' => 'Log dari :from sampai :until',
                'logs_from' => 'Log dari :from',
                'logs_until' => 'Log sampai :until',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Lihat',
                'heading' => 'Detail Error Log',
            ],
            'read' => [
                'label' => 'Baca Email',
                'subject' => 'Subjek',
                'mail_log' => 'Log Email',
                'sent_date' => 'Tanggal Kirim',
            ],
            'refresh' => [
                'label' => 'Muat Ulang',
            ],
            'clear' => [
                'label' => 'Kosongkan Log',
                'success' => 'Semua log berhasil dikosongkan.',
            ],
            'copy_markdown' => [
                'label' => 'Salin sebagai Markdown',
                'success' => 'Markdown berhasil disalin',
                'headers' => [
                    'file' => 'File',
                    'message' => 'Pesan',
                    'description' => 'Deskripsi',
                    'context' => 'Konteks',
                    'stack_trace' => 'Stack Trace',
                    'mail' => 'Detail Email',
                ],
            ],
        ],
    ],
    'schema' => [
        'error-log' => [
            'stack' => 'Stack Trace',
        ],
        'json-log' => [
            'context' => 'Konteks',
        ],
    ],
    'mail' => [
        'sender' => [
            'label' => 'Pengirim',
            'name' => 'Nama',
            'email' => 'Email',
        ],
        'receiver' => [
            'label' => 'Penerima',
            'name' => 'Nama',
            'email' => 'Email',
        ],
        'content' => 'Konten',
        'plain' => 'Teks Biasa',
        'html' => 'HTML',
    ],
    'levels' => [
        'all' => 'Semua Log',
        'alert' => 'Alert',
        'critical' => 'Critical',
        'debug' => 'Debug',
        'emergency' => 'Emergency',
        'error' => 'Error',
        'info' => 'Info',
        'notice' => 'Notice',
        'warning' => 'Warning',
        'mail' => 'Email',
    ],
];

<?php

declare(strict_types=1);

return [
    'placeholder' => 'T/A',
    'navigation' => [
        'title' => 'Log Sistem',
        'heading' => 'Tabel Log',
        'subheading' => '',
        'group' => 'Sistem',
        'label' => 'Log Sistem',
    ],
    'table' => [
        'model_label' => 'log',
        'plural_model_label' => 'log',
        'columns' => [
            'log_level' => 'Tingkat Log',
            'env' => 'Lingkungan',
            'file' => 'Nama Berkas',
            'message' => 'Ringkasan',
            'date' => 'Waktu Kejadian',
        ],
        'filters' => [
            'env' => [
                'label' => 'Lingkungan',
                'indicator' => 'Difilter berdasarkan lingkungan',
            ],
            'file' => [
                'label' => 'Berkas',
                'indicator' => 'Difilter berdasarkan berkas',
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
                'heading' => 'Log Galat',
            ],
            'read' => [
                'label' => 'Baca Surel',
                'subject' => 'Subjek',
                'mail_log' => 'Log Surel',
                'sent_date' => 'Tanggal Kirim',
            ],
            'refresh' => [
                'label' => 'Muat Ulang',
            ],
            'clear' => [
                'label' => 'Bersihkan Log',
                'success' => 'Semua log berhasil dibersihkan!',
            ],
            'copy_markdown' => [
                'label' => 'Salin sebagai Markdown',
                'success' => 'Markdown disalin ke papan klip',
                'headers' => [
                    'file' => 'Berkas',
                    'message' => 'Pesan',
                    'description' => 'Deskripsi',
                    'context' => 'Konteks',
                    'stack_trace' => 'Jejak Tumpukan',
                    'mail' => 'Detail Surel',
                ],
            ],
        ],
    ],
    'schema' => [
        'error-log' => [
            'stack' => 'Jejak Tumpukan',
        ],
        'json-log' => [
            'context' => 'Konteks',
        ],
    ],
    'mail' => [
        'sender' => [
            'label' => 'Pengirim',
            'name' => 'Nama',
            'email' => 'Surel',
        ],
        'receiver' => [
            'label' => 'Penerima',
            'name' => 'Nama',
            'email' => 'Surel',
        ],
        'content' => 'Konten',
        'plain' => 'Teks Biasa',
        'html' => 'HTML',
    ],
    'levels' => [
        'all' => 'Semua Log',
        'alert' => 'Peringatan Keras',
        'critical' => 'Kritis',
        'debug' => 'Debug',
        'emergency' => 'Darurat',
        'error' => 'Galat',
        'info' => 'Info',
        'notice' => 'Pemberitahuan',
        'warning' => 'Peringatan',
        'mail' => 'Surel',
    ],
];

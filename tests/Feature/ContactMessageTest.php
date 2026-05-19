<?php

use App\Models\ContactMessage;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\from;

it('visitors can submit the contact form', function () {
    from(route('contact'))
        ->post(route('contact.store'), [
            'name' => 'Pengunjung Demo',
            'email' => 'pengunjung@example.com',
            'phone' => '081234567890',
            'subject' => 'Pertanyaan layanan koleksi',
            'message' => 'Saya ingin menanyakan proses pembaruan data akun pada layanan ruang baca.',
        ])
        ->assertRedirect(route('contact'))
        ->assertSessionHas('inertia.flash_data.toast.message', 'Pesan Anda berhasil dikirim. Tim pengelola perpustakaan akan segera menindaklanjuti.');

    assertDatabaseHas(ContactMessage::class, [
        'name' => 'Pengunjung Demo',
        'email' => 'pengunjung@example.com',
        'subject' => 'Pertanyaan layanan koleksi',
        'status' => ContactMessage::STATUS_NEW,
    ]);
});

it('contact form validates required fields', function () {
    from(route('contact'))
        ->post(route('contact.store'), [
            'name' => '',
            'email' => 'email-tidak-valid',
            'subject' => 'Hai',
            'message' => 'Terlalu pendek',
        ])
        ->assertRedirect(route('contact'))
        ->assertSessionHasErrors([
            'name',
            'email',
            'subject',
            'message',
        ]);
});

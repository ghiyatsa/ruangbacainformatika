<?php

use App\Models\WhatsAppMessageLog;

use function Pest\Laravel\artisan;

it('prunes whatsapp logs older than the retention period', function () {
    $old = WhatsAppMessageLog::query()->create([
        'category' => 'loan_receipt',
        'status' => WhatsAppMessageLog::StatusSent,
        'phone_number_hash' => hash('sha256', '08123456789'),
        'phone_number_masked' => '0812*****89',
        'sent_at' => now()->subDays(100),
    ]);
    $old->forceFill(['created_at' => now()->subDays(100)])->save();

    $recent = WhatsAppMessageLog::query()->create([
        'category' => 'loan_receipt',
        'status' => WhatsAppMessageLog::StatusSent,
        'phone_number_hash' => hash('sha256', '08123456789'),
        'phone_number_masked' => '0812*****89',
        'sent_at' => now()->subDay(),
    ]);
    $recent->forceFill(['created_at' => now()->subDay()])->save();

    artisan('app:prune-whatsapp-logs')
        ->expectsOutput('Berhasil menghapus 1 log WhatsApp yang sudah lama.')
        ->assertExitCode(0);

    expect(WhatsAppMessageLog::find($old->id))->toBeNull();
    expect(WhatsAppMessageLog::find($recent->id))->not->toBeNull();
});

it('respects the retention-days option', function () {
    $old = WhatsAppMessageLog::query()->create([
        'category' => 'loan_receipt',
        'status' => WhatsAppMessageLog::StatusSent,
        'phone_number_hash' => hash('sha256', '08123456789'),
        'phone_number_masked' => '0812*****89',
        'sent_at' => now()->subDays(8),
    ]);
    $old->forceFill(['created_at' => now()->subDays(8)])->save();

    $recent = WhatsAppMessageLog::query()->create([
        'category' => 'loan_receipt',
        'status' => WhatsAppMessageLog::StatusSent,
        'phone_number_hash' => hash('sha256', '08123456789'),
        'phone_number_masked' => '0812*****89',
        'sent_at' => now()->subDays(6),
    ]);
    $recent->forceFill(['created_at' => now()->subDays(6)])->save();

    artisan('app:prune-whatsapp-logs --retention-days=7')
        ->expectsOutput('Berhasil menghapus 1 log WhatsApp yang sudah lama.')
        ->assertExitCode(0);

    expect(WhatsAppMessageLog::find($old->id))->toBeNull();
    expect(WhatsAppMessageLog::find($recent->id))->not->toBeNull();
});

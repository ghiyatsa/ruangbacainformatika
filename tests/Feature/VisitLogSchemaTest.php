<?php

use Illuminate\Support\Facades\Schema;

it('initializes all visit log columns required by the kiosk flow', function () {
    expect(Schema::hasColumns('visit_logs', [
        'id',
        'kiosk_device_id',
        'name',
        'visitor_type',
        'identity_number',
        'institution',
        'phone',
        'purpose',
        'notes',
        'visited_at',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

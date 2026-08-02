<?php

use App\Models\User;

it('includes mhs students outside teknik informatika in pending manual approval', function () {
    $user = User::factory()->create([
        'email' => 'student.110180123@mhs.unimal.ac.id',
        'is_approved' => false,
    ]);

    expect(User::query()->pendingMemberApproval()->pluck('id'))->toContain($user->id);
});

it('includes unimal staff emails in pending manual approval', function () {
    $user = User::factory()->create([
        'email' => 'staff@unimal.ac.id',
        'is_approved' => false,
    ]);

    expect(User::query()->pendingMemberApproval()->pluck('id'))->toContain($user->id);
});

it('excludes teknik informatika students from pending manual approval', function () {
    $user = User::factory()->create([
        'email' => 'student.110170123@mhs.unimal.ac.id',
        'is_approved' => false,
    ]);

    expect(User::query()->pendingMemberApproval()->pluck('id'))->not->toContain($user->id);
});

it('excludes non campus emails from pending manual approval', function () {
    $user = User::factory()->create([
        'email' => 'someone@gmail.com',
        'is_approved' => false,
    ]);

    expect(User::query()->pendingMemberApproval()->pluck('id'))->not->toContain($user->id);
});

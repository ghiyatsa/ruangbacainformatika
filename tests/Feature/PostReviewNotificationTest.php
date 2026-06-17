<?php

use App\Models\Post;
use App\Models\User;
use App\Notifications\PostApprovedNotification;
use App\Notifications\PostRejectedNotification;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

function makeReviewMember(): User
{
    $role = Role::firstOrCreate([
        'name' => 'member',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create([
        'email' => 'member.'.rand(1000, 9999).'@mhs.unimal.ac.id',
        'is_approved' => true,
        'whatsapp_verified_at' => now(),
        'profile_completed_at' => now(),
    ]);

    $user->assignRole($role);

    return $user;
}

function makeReviewStaff(): User
{
    $role = Role::firstOrCreate([
        'name' => 'staff',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create([
        'email' => 'staff.'.rand(1000, 9999).'@unimal.ac.id',
        'is_approved' => true,
        'whatsapp_verified_at' => now(),
        'profile_completed_at' => now(),
    ]);

    $user->assignRole($role);

    return $user;
}

it('sends database notification when a post is approved', function () {
    Notification::fake();

    $member = makeReviewMember();
    $staff = makeReviewStaff();
    $post = Post::factory()->pending()->create([
        'user_id' => $member->id,
        'title' => 'Artikel Disetujui',
    ]);

    actingAs($staff);

    $post->update([
        'status' => Post::STATUS_APPROVED,
        'reviewed_by_user_id' => $staff->id,
        'reviewed_at' => now(),
        'rejection_reason' => null,
    ]);

    Notification::assertSentTo($member, PostApprovedNotification::class);
});

it('sends database notification when a post is rejected', function () {
    Notification::fake();

    $member = makeReviewMember();
    $staff = makeReviewStaff();
    $post = Post::factory()->pending()->create([
        'user_id' => $member->id,
        'title' => 'Artikel Ditolak',
    ]);

    actingAs($staff);

    $post->update([
        'status' => Post::STATUS_REJECTED,
        'reviewed_by_user_id' => $staff->id,
        'reviewed_at' => now(),
        'rejection_reason' => 'Perlu revisi struktur.',
    ]);

    Notification::assertSentTo($member, PostRejectedNotification::class);
});

it('public notification center exposes blog review notifications', function () {
    $member = makeReviewMember();

    $approvedPost = Post::factory()->published()->create([
        'user_id' => $member->id,
        'title' => 'Artikel Approved Notif',
    ]);
    $rejectedPost = Post::factory()->create([
        'user_id' => $member->id,
        'title' => 'Artikel Rejected Notif',
        'status' => Post::STATUS_REJECTED,
        'rejection_reason' => 'Rapikan argumen.',
    ]);

    $member->notifyNow(new PostApprovedNotification($approvedPost));
    $member->notifyNow(new PostRejectedNotification($rejectedPost));

    actingAs($member)
        ->getJson(route('notifications.index'))
        ->assertOk()
        ->assertJsonPath('unreadCount', 2)
        ->assertJsonCount(2, 'notifications');

    $titles = collect(
        actingAs($member)
            ->getJson(route('notifications.index'))
            ->json('notifications'),
    )->pluck('title');

    expect($titles)
        ->toContain('Artikel diterbitkan')
        ->toContain('Artikel perlu perbaikan');
});

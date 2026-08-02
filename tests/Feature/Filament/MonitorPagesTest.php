<?php

use App\Filament\Widgets\FailedJobsTableWidget;
use App\Filament\Widgets\ScheduledTasksTableWidget;
use App\Models\FailedJob;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule as ScheduleFacade;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;

use function Pest\Laravel\actingAs;

function createMonitorAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

function makeFailedJob(array $attributes = []): FailedJob
{
    return FailedJob::create(array_merge([
        'uuid' => (string) Str::uuid(),
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode([
            'uuid' => (string) Str::uuid(),
            'displayName' => 'App\\Jobs\\ExampleReminderJob',
        ]),
        'exception' => "RuntimeException: Gagal mengirim WhatsApp\n  at app/Services/WhatsAppGateway.php:42",
        'failed_at' => now()->subMinutes(5),
    ], $attributes));
}

it('non admin users can not access the monitor pages', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/admin/monitor-queue')
        ->assertForbidden();

    actingAs($user)
        ->get('/admin/monitor-schedule')
        ->assertForbidden();
});

it('super admin users can access the queue monitor page', function () {
    $user = createMonitorAdmin();

    actingAs($user)
        ->get('/admin/monitor-queue')
        ->assertOk()
        ->assertSee('Monitor Antrian')
        ->assertSee('Job Antre')
        ->assertSee('Job Gagal')
        ->assertSee('Batch Aktif')
        ->assertSee('Tidak ada job gagal');
});

it('super admin users can access the schedule monitor page', function () {
    $user = createMonitorAdmin();

    actingAs($user)
        ->get('/admin/monitor-schedule')
        ->assertOk()
        ->assertSee('Monitor Penjadwalan')
        ->assertSee('Task Terjadwal')
        ->assertSee('Sehat')
        ->assertSee('Perlu Perhatian')
        ->assertSee('Belum Pernah Jalan')
        ->assertSee('Belum ada riwayat run');
});

it('schedule monitor page shows logged runs with status', function () {
    $user = createMonitorAdmin();

    $task = MonitoredScheduledTask::create([
        'name' => 'app:remind-return',
        'type' => 'command',
        'cron_expression' => '0 8 * * *',
        'timezone' => 'Asia/Jakarta',
        'grace_time_in_minutes' => 5,
        'last_finished_at' => now(),
    ]);

    MonitoredScheduledTaskLogItem::create([
        'monitored_scheduled_task_id' => $task->id,
        'type' => MonitoredScheduledTaskLogItem::TYPE_FINISHED,
        'meta' => ['runtime' => 1.5, 'memory' => 1048576, 'output' => null],
    ]);

    MonitoredScheduledTaskLogItem::create([
        'monitored_scheduled_task_id' => $task->id,
        'type' => MonitoredScheduledTaskLogItem::TYPE_FAILED,
        'meta' => ['failure_message' => 'Token similarity tidak valid.'],
    ]);

    actingAs($user)
        ->get('/admin/monitor-schedule')
        ->assertOk()
        ->assertSee('app:remind-return')
        ->assertSee('Selesai')
        ->assertSee('Gagal')
        ->assertSee('Token similarity tidak valid.');
});

it('queue monitor page shows failed job details', function () {
    $user = createMonitorAdmin();
    makeFailedJob();

    actingAs($user)
        ->get('/admin/monitor-queue')
        ->assertOk()
        ->assertSee('App\\Jobs\\ExampleReminderJob')
        ->assertSee('RuntimeException: Gagal mengirim WhatsApp')
        ->assertSee('default');
});

it('super admin users can retry a single failed job', function () {
    $user = createMonitorAdmin();
    $failedJob = makeFailedJob();

    actingAs($user);

    Livewire::test(FailedJobsTableWidget::class)
        ->assertCanSeeTableRecords([$failedJob])
        ->callTableAction('retry', $failedJob)
        ->assertNotified('Job dikirim ulang ke antrean');

    expect(FailedJob::find($failedJob->id))->toBeNull()
        ->and(DB::table('jobs')->count())->toBe(1);
});

it('super admin users can delete a single failed job', function () {
    $user = createMonitorAdmin();
    $failedJob = makeFailedJob();

    actingAs($user);

    Livewire::test(FailedJobsTableWidget::class)
        ->callTableAction('delete', $failedJob)
        ->assertNotified('Job gagal dihapus');

    expect(FailedJob::find($failedJob->id))->toBeNull()
        ->and(DB::table('jobs')->count())->toBe(0);
});

it('super admin users can retry all failed jobs', function () {
    $user = createMonitorAdmin();
    $first = makeFailedJob();
    $second = makeFailedJob();

    actingAs($user);

    Livewire::test(FailedJobsTableWidget::class)
        ->callTableAction('retryAll')
        ->assertNotified('Seluruh job gagal dikirim ulang');

    expect(FailedJob::count())->toBe(0)
        ->and(DB::table('jobs')->count())->toBe(2);
});

it('super admin users can purge all failed jobs', function () {
    $user = createMonitorAdmin();
    makeFailedJob();
    makeFailedJob();

    actingAs($user);

    Livewire::test(FailedJobsTableWidget::class)
        ->callTableAction('purge')
        ->assertNotified('Seluruh job gagal dibersihkan');

    expect(FailedJob::count())->toBe(0)
        ->and(DB::table('jobs')->count())->toBe(0);
});

it('schedule monitor page lists scheduled tasks with next run', function () {
    $user = createMonitorAdmin();

    MonitoredScheduledTask::create([
        'name' => 'app:prune-notifications',
        'type' => 'command',
        'cron_expression' => '0 3 * * *',
        'timezone' => 'Asia/Jakarta',
        'grace_time_in_minutes' => 5,
    ]);

    actingAs($user)
        ->get('/admin/monitor-schedule')
        ->assertOk()
        ->assertSee('app:prune-notifications')
        ->assertSee('0 3 * * *')
        ->assertSee('Belum pernah')
        ->assertSee('Sinkronkan Jadwal')
        ->assertSee('Jalankan Sekarang');
});

it('super admin users can run a scheduled task now', function () {
    $user = createMonitorAdmin();

    $task = MonitoredScheduledTask::create([
        'name' => 'app:prune-notifications',
        'type' => 'command',
        'cron_expression' => '0 3 * * *',
        'timezone' => 'Asia/Jakarta',
        'grace_time_in_minutes' => 5,
    ]);

    actingAs($user);

    Livewire::test(ScheduledTasksTableWidget::class)
        ->assertCanSeeTableRecords([$task])
        ->callTableAction('runNow', $task)
        ->assertNotified('Task app:prune-notifications telah dijalankan');
});

it('can run a scheduled task now when the schedule is not loaded yet', function () {
    $user = createMonitorAdmin();

    $task = MonitoredScheduledTask::create([
        'name' => 'app:prune-notifications',
        'type' => 'command',
        'cron_expression' => '0 3 * * *',
        'timezone' => 'Asia/Jakarta',
        'grace_time_in_minutes' => 5,
    ]);

    App::instance(Schedule::class, new Schedule('Asia/Jakarta'));
    ScheduleFacade::clearResolvedInstance(Schedule::class);

    actingAs($user);

    Livewire::test(ScheduledTasksTableWidget::class)
        ->assertCanSeeTableRecords([$task])
        ->callTableAction('runNow', $task)
        ->assertNotified('Task app:prune-notifications telah dijalankan');
});

it('super admin users can sync the schedule from the widget', function () {
    $user = createMonitorAdmin();

    actingAs($user);

    Livewire::test(ScheduledTasksTableWidget::class)
        ->callTableAction('syncSchedule')
        ->assertNotified('Jadwal disinkronkan');
});

<?php

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Livewire\DatabaseNotifications;
use Filament\Notifications\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

it('shows admin filament notifications in both the admin and dashboard panels', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    Notification::make()->title('Notif Admin')->body('Isi')->sendToDatabase($user);

    actingAs($user);

    Filament::setCurrentPanel('admin');
    $adminComponent = Livewire::test(DatabaseNotifications::class)->instance();
    expect($adminComponent->getNotifications())->toHaveCount(1);

    Filament::setCurrentPanel('dashboard');
    $dashboardComponent = Livewire::test(DatabaseNotifications::class)->instance();
    expect($dashboardComponent->getNotifications())->toHaveCount(1);
});

it('does not show non-filament public notifications in the panel bells', function () {
    $user = User::factory()->create();

    $user->notify(new class extends Illuminate\Notifications\Notification
    {
        public function via($notifiable): array
        {
            return ['database'];
        }

        public function toArray($notifiable): array
        {
            return ['title' => 'Notifikasi Publik', 'message' => 'Isi'];
        }
    });

    actingAs($user);

    Filament::setCurrentPanel('dashboard');
    $component = Livewire::test(DatabaseNotifications::class)->instance();

    expect($component->getNotifications())->toHaveCount(0);
});

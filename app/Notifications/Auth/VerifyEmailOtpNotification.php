<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Throwable;

class VerifyEmailOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 12;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $otp = random_int(100000, 999999);

        Cache::put("email_verification_otp_{$notifiable->id}", Hash::make((string) $otp), now()->addMinutes(15));

        return (new MailMessage)
            ->subject('Verifikasi Email - Kode OTP')
            ->greeting('Halo '.$notifiable->name.'!')
            ->line('Gunakan kode OTP berikut untuk memverifikasi alamat email Anda. Kode ini berlaku selama 15 menit.')
            ->line('Kode OTP: **'.$otp.'**')
            ->line('Jika Anda tidak membuat akun, tidak ada tindakan lebih lanjut yang diperlukan.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [300, 900, 1800, 3600];
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHours(12);
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception) {
            report($exception);
        }
    }
}

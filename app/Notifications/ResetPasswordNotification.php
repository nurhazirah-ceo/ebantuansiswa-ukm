<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    use Queueable;

    protected function resetUrl($notifiable)
    {
        return route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->from(config('mail.from.address'), 'eBantuanSiswa UKM')
            ->subject('Tetapan Semula Kata Laluan Akaun Bantuan')
            ->markdown('emails.password-reset', [
                'url' => $this->resetUrl($notifiable),
            ]);
    }
}

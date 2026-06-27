<?php

namespace App\Notifications;

use App\Models\Permohonan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AgihanSelesaiNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Permohonan $permohonan)
    {
        $this->permohonan->loadMissing(['pelajar', 'bantuan']);
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Bantuan Anda Telah Diagihkan')
            ->greeting('Salam sejahtera ' . ($this->permohonan->pelajar?->nama_penuh ?? 'Pelajar') . ',')
            ->line('Bantuan anda telah diagihkan oleh pihak pentadbir.')
            ->line('Nama pelajar: ' . ($this->permohonan->pelajar?->nama_penuh ?? '-'))
            ->line('No Kelompok: ' . ($this->permohonan->no_kelompok ?? '-'))
            ->line('Jenis Bantuan: ' . Permohonan::jenisBantuanLabel($this->permohonan->bantuan?->jenis_bantuan ?? $this->permohonan->jenis_bantuan))
            ->line('Kategori Bantuan: ' . Permohonan::kategoriBantuanLabel($this->permohonan->bantuan?->kategori_bantuan))
            ->line('Tarikh Agihan: ' . ($this->permohonan->tarikh_agihan?->format('d/m/Y h:i A') ?? '-'));

        if (filled($this->permohonan->catatan_agihan)) {
            $message->line('Catatan admin: ' . $this->permohonan->catatan_agihan);
        }

        return $message->line('Terima kasih kerana menggunakan sistem eBantuanSiswa UKM.');
    }
}

<?php

namespace App\Notifications;

use App\Models\Permohonan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PermohonanKeputusanNotification extends Notification
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
        $status = $this->permohonan->status === 'Ditolak' ? 'Ditolak' : 'Diluluskan';
        $subject = 'Keputusan Permohonan Bantuan - ' . $status;
        $catatanLabel = $status === 'Ditolak'
            ? 'Sebab/justifikasi penolakan'
            : 'Justifikasi/catatan admin';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Salam sejahtera ' . ($this->permohonan->pelajar?->nama_penuh ?? 'Pelajar') . ',')
            ->line('Keputusan permohonan bantuan anda telah dikemaskini.')
            ->line('Nama pelajar: ' . ($this->permohonan->pelajar?->nama_penuh ?? '-'))
            ->line('No Kelompok: ' . ($this->permohonan->no_kelompok ?? '-'))
            ->line('Jenis Bantuan: ' . $this->formatLabel($this->permohonan->bantuan?->jenis_bantuan ?? $this->permohonan->jenis_bantuan))
            ->line('Kategori Bantuan: ' . $this->formatLabel($this->permohonan->bantuan?->kategori_bantuan))
            ->line('Status: ' . $status)
            ->line($catatanLabel . ': ' . ($this->permohonan->admin_catatan ?? $this->permohonan->catatan ?? '-'))
            ->line('Sila semak halaman Status Permohonan untuk maklumat terkini.');
    }

    private function formatLabel(?string $value): string
    {
        if (! filled($value)) {
            return '-';
        }

        return Str::of($value)
            ->replace(['_', '-'], ' ')
            ->squish()
            ->title()
            ->toString();
    }
}

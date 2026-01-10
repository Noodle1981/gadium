<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CriticalQualityAlert extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public \App\Models\Project $project,
        public float $errorRate
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->error()
                    ->subject("Alerta de Calidad: Proyecto {$this->project->id}")
                    ->line("El proyecto '{$this->project->name}' ha superado el umbral de tolerancia de defectos.")
                    ->line("Tasa de Error Actual: {$this->errorRate}%")
                    ->action('Ver Detalles del Proyecto', url('/dashboard'))
                    ->line('Se recomienda intervenir en la línea de producción de inmediato.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'error_rate' => $this->errorRate,
            'message' => "Alerta Crítica: El proyecto {$this->project->id} excede el 20% de errores.",
        ];
    }
}

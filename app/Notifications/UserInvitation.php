<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class UserInvitation extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
        // Generar URL firmada con expiración de 24 horas
        $url = URL::temporarySignedRoute(
            'password.setup',
            now()->addHours(24),
            ['email' => $notifiable->email]
        );

        return (new MailMessage)
                    ->subject('Invitación a Gadium - Configura tu contraseña')
                    ->greeting('¡Bienvenido a Gadium!')
                    ->line('Has sido invitado a unirte a la plataforma Gadium.')
                    ->line('Para comenzar, necesitas configurar tu contraseña.')
                    ->action('Configurar Contraseña', $url)
                    ->line('Este enlace expirará en 24 horas.')
                    ->line('Si no solicitaste esta invitación, puedes ignorar este correo.')
                    ->salutation('Saludos, Equipo Gadium');
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
}

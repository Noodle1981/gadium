<?php

namespace App\Listeners;

use App\Events\ProductionLogCreated;
use App\Models\User;
use App\Notifications\CriticalQualityAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class MonitorQualityThreshold
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProductionLogCreated $event): void
    {
        $project = $event->log->project;
        $errorRate = $project->error_rate; // Usando el accesor definido en el modelo

        if ($errorRate > 20) {
            // Cambiar estado del proyecto
            if ($project->quality_status !== 'crítico') {
                $project->update(['quality_status' => 'crítico']);

                // Notificar a los Admins y Managers
                $usersToNotify = User::role(['Super Admin', 'Admin', 'Manager'])->get();
                Notification::send($usersToNotify, new CriticalQualityAlert($project, $errorRate));
            }
        } else {
            // Si baja del umbral (reprocesos exitosos o nuevas cargas limpias), volver a normal
            if ($project->quality_status === 'crítico') {
                $project->update(['quality_status' => 'normal']);
            }
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\NotificacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    protected NotificacionService $notificacionService;

    public function __construct(NotificacionService $notificacionService)
    {
        $this->notificacionService = $notificacionService;
    }

    /**
     * Endpoint JSON para alimentar el Centro de Notificaciones en el Topbar (Campana 🔔)
     *
     * @return JsonResponse
     */
    public function resumen(): JsonResponse
    {
        $resumen = $this->notificacionService->getResumenNotificaciones();
        return response()->json($resumen);
    }
}

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
     * @param Request $request
     * @return JsonResponse
     */
    public function resumen(Request $request): JsonResponse
    {
        $resumen = $this->notificacionService->getResumenNotificaciones();
        $etag = md5(json_encode($resumen));

        $noneMatch = $request->header('If-None-Match');
        if ($noneMatch && (trim($noneMatch, '"') === $etag)) {
            return response()->json(null, 304, [
                'ETag'          => '"' . $etag . '"',
                'Cache-Control' => 'no-cache, private',
            ]);
        }

        return response()->json($resumen, 200, [
            'ETag'          => '"' . $etag . '"',
            'Cache-Control' => 'no-cache, private',
        ]);
    }
}

<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if ($this->shouldReport($e)) {
                try {
                    \App\Models\AuditLog::log(
                        'sistema',
                        'excepcion',
                        'Excepción del sistema: ' . substr($e->getMessage(), 0, 255),
                        [
                            'exception' => get_class($e),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'code' => $e->getCode(),
                            'url' => request()?->fullUrl(),
                            'method' => request()?->method(),
                        ]
                    );
                } catch (Throwable $logEx) {
                    // Silently ignore to avoid recursive errors
                }
            }
        });

        // ─── Captura Global de Errores de Validación (HTTP 422) ───
        $this->renderable(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                $flatErrors = [];
                foreach ($e->errors() as $field => $messages) {
                    foreach ($messages as $msg) {
                        $flatErrors[] = $msg;
                    }
                }
                return response()->json([
                    'success' => false,
                    'message' => count($flatErrors) > 0 ? implode(' ', $flatErrors) : 'Por favor revisa los datos ingresados.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // ─── Captura Global de Errores HTTP Comunes (403, 404, 419, etc.) ───
        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                $status = $e->getStatusCode();
                $message = match ($status) {
                    403 => 'No tienes los permisos necesarios para realizar esta acción.',
                    404 => 'El recurso o registro solicitado no fue encontrado.',
                    419 => 'La sesión ha expirado por inactividad. Por favor recarga la página.',
                    429 => 'Demasiadas solicitudes al servidor. Por favor espera un momento.',
                    default => $e->getMessage() ?: 'Ocurrió un error al procesar la solicitud.',
                };
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], $status);
            }
        });

        // ─── Captura Global de Errores de BD y Excepciones de Servidor (500) ───
        $this->renderable(function (\Illuminate\Database\QueryException $e, $request) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug')
                        ? 'Error de base de datos: ' . $e->getMessage()
                        : 'Error en la base de datos al procesar la operación. No se aplicaron cambios.',
                ], 500);
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                // Si la excepción es de negocio (código < 500) o modo debug está activo, mostrar el mensaje específico
                $isDomainException = !($e instanceof \Error) && !($e instanceof \ErrorException);
                $friendlyMessage = ($isDomainException || config('app.debug'))
                    ? $e->getMessage()
                    : 'Ocurrió un inconveniente al procesar la solicitud. Por favor intenta nuevamente o contacta al administrador.';

                return response()->json([
                    'success' => false,
                    'message' => $friendlyMessage ?: 'Ocurrió un inconveniente al procesar la solicitud.',
                ], 500);
            }
        });
    }
}

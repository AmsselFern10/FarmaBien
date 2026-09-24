<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and inject defensive security headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // 1. Prevenir Clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 2. Prevenir MIME-Sniffing (XSS / Drive-by downloads)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 3. Habilitar filtro XSS en navegadores heredados
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // 4. Control estricto de información de Referrer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 5. Restringir APIs sensibles del navegador que la aplicación no utiliza
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // 6. Ocultar huellas del servidor
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}

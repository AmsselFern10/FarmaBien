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

        // 7. HSTS — fuerza HTTPS en el browser (31536000s = 1 año)
        //    Solo activo en producción para no romper desarrollo local HTTP.
        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // 8. Content-Security-Policy — bloquea carga de scripts/styles/frames externos
        //    'unsafe-inline' requerido por Alpine.js y Tailwind inline styles.
        //    'unsafe-eval' requerido por Alpine.js (v3 usa Function()).
        //    data: requerido para íconos SVG inline y fuentes embebidas.
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.gstatic.com",
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' data: blob:",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}

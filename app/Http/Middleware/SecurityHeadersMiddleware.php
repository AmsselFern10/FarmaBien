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

        // Assets Vite — hash en el nombre garantiza contenido inmutable.
        // El browser NO re-valida estos archivos en ninguna navegación posterior.
        if (str_starts_with($request->getPathInfo(), '/build/')) {
            $response->headers->set(
                'Cache-Control',
                'public, max-age=31536000, immutable'
            );
            return $response;
        }

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

        // 7 & 8. Solo en producción: HSTS + CSP
        // En desarrollo local, Vite sirve assets desde localhost:5173 (diferente origen)
        // y el CSP los bloquearía. En producción los assets vienen del mismo dominio.
        if (app()->environment('production')) {
            // HSTS — fuerza HTTPS en el browser (31536000s = 1 año)
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );

            // Content-Security-Policy — bloquea scripts/styles/frames externos
            // 'unsafe-inline' requerido por Alpine.js y Tailwind inline styles.
            // 'unsafe-eval' requerido por Alpine.js v3 (usa Function()).
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
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
        }

        return $response;
    }
}

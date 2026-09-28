<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ImagenController
 *
 * Sirve imágenes privadas (productos) únicamente a usuarios autenticados.
 * Las imágenes se almacenan fuera de public/ en storage/app/images/
 * y NUNCA son accesibles directamente vía URL del servidor web.
 */
class ImagenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Sirve un archivo de imagen privado.
     *
     * @param  string  $path  Ruta relativa dentro del disco private_images
     *                        ej: "productos/abc123xyz.webp"
     */
    public function serve(string $path): StreamedResponse|\Illuminate\Http\Response
    {
        // Seguridad: evitar path traversal (../../etc/passwd, etc.)
        $path = ltrim($path, '/');
        if (str_contains($path, '..')) {
            abort(403, 'Ruta no permitida.');
        }

        $disk = Storage::disk('private_images');

        if (! $disk->exists($path)) {
            abort(404, 'Imagen no encontrada.');
        }

        $mimeType = $this->detectMime($path);
        $size     = $disk->size($path);

        return response()->stream(function () use ($disk, $path) {
            $stream = $disk->readStream($path);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type'        => $mimeType,
            'Content-Length'      => $size,
            'Cache-Control'       => 'private, max-age=86400', // 24h caché en navegador, no en proxies
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Sirve imágenes de PRODUCTOS sin requerir autenticación.
     * Solo permite acceder a archivos dentro de la carpeta "productos/".
     * Usado exclusivamente en el catálogo público.
     *
     * @param  string  $path  Ruta relativa (debe comenzar con "productos/")
     */
    public function servePublic(string $path): StreamedResponse|\Illuminate\Http\Response
    {
        // Seguridad: evitar path traversal
        $path = ltrim($path, '/');
        if (str_contains($path, '..')) {
            abort(403, 'Ruta no permitida.');
        }

        // Seguridad: solo imágenes de la carpeta productos/
        if (!str_starts_with($path, 'productos/')) {
            abort(403, 'Acceso restringido.');
        }

        $disk = Storage::disk('private_images');

        if (! $disk->exists($path)) {
            abort(404, 'Imagen no encontrada.');
        }

        $mimeType = $this->detectMime($path);
        $size     = $disk->size($path);

        return response()->stream(function () use ($disk, $path) {
            $stream = $disk->readStream($path);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type'        => $mimeType,
            'Content-Length'      => $size,
            'Cache-Control'       => 'public, max-age=86400', // público: 24h en CDN/proxy también
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function detectMime(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'webp'          => 'image/webp',
            'jpg', 'jpeg'   => 'image/jpeg',
            'png'           => 'image/png',
            'gif'           => 'image/gif',
            'svg'           => 'image/svg+xml',
            default         => 'application/octet-stream',
        };
    }
}

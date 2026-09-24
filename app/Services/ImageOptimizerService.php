<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class ImageOptimizerService
{
    /**
     * Optimiza, comprime y convierte una imagen subida al formato moderno WebP.
     * Reduce dimensiones excesivas manteniendo el aspect ratio (máx 800x800).
     * 
     * @param UploadedFile $file
     * @param string $directory (ej: 'productos')
     * @param int $maxDimension (máx ancho/alto en píxeles)
     * @param int $quality (calidad de compresión WebP, 0-100)
     * @return string Ruta relativa en el disco public (ej: 'productos/abc123xyz.webp')
     */
    public function optimizarYGuardarWebp(
        UploadedFile $file,
        string $directory = 'productos',
        int $maxDimension = 800,
        int $quality = 82
    ): string {
        $filename = Str::random(40) . '.webp';
        $targetDir = Storage::disk('public')->path($directory);

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

        // Si GD y imagewebp están disponibles, procesar y comprimir
        if (extension_loaded('gd') && function_exists('imagewebp')) {
            try {
                $image = $this->crearRecursoImagenDesdeArchivo($file->getRealPath());

                if ($image !== false) {
                    $origWidth = imagesx($image);
                    $origHeight = imagesy($image);

                    // Calcular dimensiones preservando relación de aspecto
                    $newWidth = $origWidth;
                    $newHeight = $origHeight;

                    if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
                        $ratio = min($maxDimension / $origWidth, $maxDimension / $origHeight);
                        $newWidth = max(1, (int) round($origWidth * $ratio));
                        $newHeight = max(1, (int) round($origHeight * $ratio));
                    }

                    // Crear canvas optimizado con soporte de transparencia alfa
                    $resized = imagecreatetruecolor($newWidth, $newHeight);
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                    imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);

                    // Remuestreo de alta calidad
                    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

                    // Guardar como WebP optimizado
                    imagewebp($resized, $targetPath, $quality);

                    imagedestroy($image);
                    imagedestroy($resized);

                    return $directory . '/' . $filename;
                }
            } catch (Exception $e) {
                // Si falla la conversión con GD, caer en fallback de guardado estándar
            }
        }

        // Fallback: Guardado estándar si no se pudo convertir a WebP
        return $file->store($directory, 'public');
    }

    /**
     * Crear recurso de imagen GD según el tipo de archivo.
     * 
     * @param string $path
     * @return \GdImage|resource|false
     */
    protected function crearRecursoImagenDesdeArchivo(string $path)
    {
        $info = @getimagesize($path);
        if (!$info) {
            return false;
        }

        $mime = $info['mime'] ?? '';

        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png'               => @imagecreatefrompng($path),
            'image/webp'              => @imagecreatefromwebp($path),
            'image/gif'               => @imagecreatefromgif($path),
            'image/bmp', 'image/x-ms-bmp' => @imagecreatefrombmp($path),
            default                   => false,
        };
    }
}

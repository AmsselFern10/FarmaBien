<?php

namespace App\Services;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteExportService
{
    /**
     * Descarga un CSV (Excel lo abre). No requiere paquetes externos.
     *
     * @param string $filenameSinExtension Ej: "ventas_2026-02-17"
     * @param array<int,string> $columns
     * @param array<int,array<string,mixed>> $rows
     */
    public function downloadCsv(string $filenameSinExtension, array $columns, array $rows): StreamedResponse
    {
        $filename = $filenameSinExtension . '.csv';

        return response()->streamDownload(function () use ($columns, $rows) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 para Excel
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, $columns, ';');

            foreach ($rows as $row) {
                $line = [];
                foreach ($columns as $col) {
                    $line[] = $row[$col] ?? '';
                }
                fputcsv($out, $line, ';');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    
    /**
     * Descarga un Excel REAL (.xlsx).
     * Requiere: composer require phpoffice/phpspreadsheet
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|null
     */
    public function downloadExcel(string $filenameSinExtension, string $titulo, array $columns, array $rows)
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            return null;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Excel: máximo 31 chars y sin caracteres inválidos
        $safeTitle = preg_replace('/[\\\\:\\/\\?\\*\\[\\]]/', ' ', $titulo);
        $safeTitle = trim($safeTitle ?: 'Reporte');
        $sheet->setTitle(mb_substr($safeTitle, 0, 31));

        // Header
        $sheet->fromArray($columns, null, 'A1', true);

        // Rows
        $r = 2;
        foreach ($rows as $row) {
            $line = [];
            foreach ($columns as $col) {
                $val = $row[$col] ?? '';
                if (is_bool($val)) $val = $val ? 1 : 0;
                if (is_array($val) || is_object($val)) $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                $line[] = $val;
            }
            $sheet->fromArray($line, null, 'A' . $r, true);
            $r++;
        }

        $colCount = max(1, count($columns));
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

        // Estilos básicos
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:' . $lastCol . '1');

        // AutoSize columnas (mejor UX)
        for ($i = 1; $i <= $colCount; $i++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        // Formato numérico simple (si toda la columna es numérica)
        for ($i = 1; $i <= $colCount; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $allNumeric = true;
            for ($rr = 2; $rr < $r; $rr++) {
                $v = $sheet->getCell($colLetter . $rr)->getValue();
                if ($v === '' || $v === null) continue;
                if (!is_numeric($v)) { $allNumeric = false; break; }
            }
            if ($allNumeric && $r > 2) {
                $sheet->getStyle($colLetter . '2:' . $colLetter . ($r - 1))
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
                $sheet->getStyle($colLetter . '2:' . $colLetter . ($r - 1))
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            }
        }

        $tmp = tempnam(sys_get_temp_dir(), 'fb_xlsx_');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tmp);

        $filename = $filenameSinExtension . '.xlsx';

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }


/**
     * Descarga un PDF simple a partir de una tabla HTML.
     * Requiere que exista Dompdf (barryvdh/laravel-dompdf o dompdf/dompdf).
     *
     * @return Response|null
     */
    public function downloadPdfTable(string $filenameSinExtension, string $titulo, array $columns, array $rows): ?Response
    {
        if (!class_exists(\Dompdf\Dompdf::class)) {
            return null;
        }

        $html = $this->buildHtmlTable($titulo, $columns, $rows);

        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
        ]);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $output = $dompdf->output();
        $filename = $filenameSinExtension . '.pdf';

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildHtmlTable(string $titulo, array $columns, array $rows): string
    {
        $thead = '';
        foreach ($columns as $c) {
            $thead .= '<th>' . htmlspecialchars((string) $c, ENT_QUOTES, 'UTF-8') . '</th>';
        }

        $tbody = '';
        foreach ($rows as $row) {
            $tbody .= '<tr>';
            foreach ($columns as $c) {
                $val = $row[$c] ?? '';
                $tbody .= '<td>' . htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $tbody .= '</tr>';
        }

        $titleEsc = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
        $date = date('Y-m-d H:i');

        return <<<HTML
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
    h1 { font-size: 14px; margin: 0 0 6px 0; }
    .meta { font-size: 9px; margin: 0 0 10px 0; color: #444; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 4px 6px; vertical-align: top; }
    th { background: #f3f3f3; font-weight: bold; }
    tr:nth-child(even) td { background: #fafafa; }
  </style>
</head>
<body>
  <h1>{$titleEsc}</h1>
  <p class="meta">Generado: {$date}</p>
  <table>
    <thead><tr>{$thead}</tr></thead>
    <tbody>{$tbody}</tbody>
  </table>
</body>
</html>
HTML;
    }
}

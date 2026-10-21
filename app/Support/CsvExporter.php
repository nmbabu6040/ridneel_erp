<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Plain CSV export — no package required (unlike a true .xlsx, which
 * needs something like PhpSpreadsheet/Laravel Excel; this environment
 * had no internet access to `composer require` one). CSV opens
 * natively in Excel, Google Sheets and LibreOffice, which covers the
 * actual need ("get this data into a spreadsheet") without adding a
 * dependency. A UTF-8 BOM is written first so Bengali text displays
 * correctly when the file is opened directly in Excel on Windows,
 * which otherwise mis-detects the encoding.
 */
class CsvExporter
{
    public static function stream(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return new StreamedResponse(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, $headers);

            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

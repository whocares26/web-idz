<?php

declare(strict_types=1);

namespace App\Service\Report;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class CsvReportGenerator implements ReportGeneratorInterface
{
    public function __construct(
        private OrderRowFormatter $formatter,
    ) {
    }

    public function format(): string
    {
        return 'csv';
    }

    public function generate(iterable $orders): Response
    {
        $headers = $this->formatter->headers();
        $rows = $this->formatter->rows($orders);

        $response = new StreamedResponse(static function () use ($headers, $rows): void {
            $out = fopen('php://output', 'w');
            // BOM so Excel opens UTF-8 CSV correctly.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers, ';', '"', '\\');
            foreach ($rows as $row) {
                fputcsv($out, $row, ';', '"', '\\');
            }
            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="orders.csv"');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }
}

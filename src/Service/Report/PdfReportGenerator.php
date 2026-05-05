<?php

declare(strict_types=1);

namespace App\Service\Report;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Response;

final readonly class PdfReportGenerator implements ReportGeneratorInterface
{
    public function __construct(
        private OrderRowFormatter $formatter,
    ) {
    }

    public function format(): string
    {
        return 'pdf';
    }

    public function generate(iterable $orders): Response
    {
        $headers = $this->formatter->headers();
        $rows = $this->formatter->rows($orders);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'default_font' => 'dejavusans',
            'default_font_size' => 7,
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'tempDir' => sys_get_temp_dir(),
        ]);
        $mpdf->WriteHTML($this->buildHtml($headers, $rows));
        $body = $mpdf->Output('', Destination::STRING_RETURN);

        $response = new Response($body);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="orders.pdf"');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }

    /**
     * @param list<string> $headers
     * @param list<list<string|int>> $rows
     */
    private function buildHtml(array $headers, array $rows): string
    {
        $css = <<<'CSS'
<style>
    body { font-family: dejavusans; font-size: 7pt; }
    h1 { background: #000; color: #fff; padding: 8pt; text-align: center; font-size: 12pt; margin: 0 0 6pt; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #000; padding: 2pt 3pt; vertical-align: top; }
    th { background: #333; color: #fff; text-align: center; }
    tr.alt td { background: #f5f5f5; }
</style>
CSS;

        $thead = '<tr>';
        foreach ($headers as $header) {
            $thead .= '<th>'.htmlspecialchars($header, ENT_QUOTES, 'UTF-8').'</th>';
        }
        $thead .= '</tr>';

        $tbody = '';
        $alt = false;
        foreach ($rows as $row) {
            $tbody .= '<tr'.($alt ? ' class="alt"' : '').'>';
            foreach ($row as $cell) {
                $tbody .= '<td>'.htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8').'</td>';
            }
            $tbody .= '</tr>';
            $alt = !$alt;
        }

        return $css
            .'<h1>Отчёт по заказам</h1>'
            .'<table><thead>'.$thead.'</thead><tbody>'.$tbody.'</tbody></table>';
    }
}

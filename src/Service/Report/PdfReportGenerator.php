<?php

declare(strict_types=1);

namespace App\Service\Report;

use Fpdf\Fpdf;
use Symfony\Component\HttpFoundation\Response;

final readonly class PdfReportGenerator implements ReportGeneratorInterface
{
    /** @var list<int> */
    private const COL_WIDTHS = [7, 25, 22, 14, 42, 18, 36, 40, 10, 8, 20, 24];
    /** @var list<string> */
    private const HEADERS_LATIN = [
        'No', 'Name', 'Phone', 'City', 'Address', 'Delivery',
        'Payment', 'Item', 'Size', 'Qty', 'Sum (RUB)', 'Date',
    ];
    private const LINE_HEIGHT = 4.5;

    public function __construct(
        private OrderRowFormatter $formatter,
        private Transliterator $transliterator,
    ) {
    }

    public function format(): string
    {
        return 'pdf';
    }

    public function generate(iterable $orders): Response
    {
        $rows = $this->formatter->rows($orders);

        $pdf = new Fpdf('L', 'mm', 'A4');
        $pdf->SetMargins(8, 8, 8);
        $pdf->AddPage();

        $this->renderTitle($pdf);
        $this->renderHeader($pdf);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 6.5);

        $alternate = false;
        foreach ($rows as $row) {
            $rowHeight = $this->measureRowHeight($pdf, $row);

            if ($pdf->GetY() + $rowHeight > $pdf->GetPageHeight() - 10) {
                $pdf->AddPage();
                $this->renderHeader($pdf);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('Arial', '', 6.5);
            }

            $this->renderRow($pdf, $row, $alternate);
            $alternate = !$alternate;
        }

        $body = $pdf->Output('S');

        $response = new Response($body);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="orders.pdf"');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }

    private function renderTitle(Fpdf $pdf): void
    {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'Orders Report', 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(3);
    }

    private function renderHeader(Fpdf $pdf): void
    {
        $pdf->SetFillColor(50, 50, 50);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 7);
        foreach (self::HEADERS_LATIN as $i => $header) {
            $pdf->Cell(self::COL_WIDTHS[$i], 7, $header, 1, 0, 'C', true);
        }
        $pdf->Ln();
    }

    /**
     * @param list<string|int> $row
     */
    private function measureRowHeight(Fpdf $pdf, array $row): float
    {
        $maxLines = 1;
        foreach ($row as $i => $value) {
            $text = $this->transliterator->transliterate((string) $value);
            $words = explode(' ', $text);
            $lines = 1;
            $current = '';
            foreach ($words as $word) {
                $candidate = $current === '' ? $word : $current.' '.$word;
                if ($pdf->GetStringWidth($candidate) > self::COL_WIDTHS[$i] - 2) {
                    ++$lines;
                    $current = $word;
                } else {
                    $current = $candidate;
                }
            }
            $maxLines = max($maxLines, $lines);
        }

        return $maxLines * self::LINE_HEIGHT + 2;
    }

    /**
     * @param list<string|int> $row
     */
    private function renderRow(Fpdf $pdf, array $row, bool $alternate): void
    {
        $rowHeight = $this->measureRowHeight($pdf, $row);
        $color = $alternate ? [245, 245, 245] : [255, 255, 255];

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        foreach ($row as $i => $value) {
            $text = $this->transliterator->transliterate((string) $value);
            $pdf->SetFillColor($color[0], $color[1], $color[2]);
            $pdf->MultiCell(self::COL_WIDTHS[$i], self::LINE_HEIGHT, $text, 1, 'L', true);
            $x += self::COL_WIDTHS[$i];
            $pdf->SetXY($x, $y);
        }
        $pdf->SetXY(8, $y + $rowHeight);
    }
}

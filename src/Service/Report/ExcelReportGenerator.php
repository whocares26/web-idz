<?php

declare(strict_types=1);

namespace App\Service\Report;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

final readonly class ExcelReportGenerator implements ReportGeneratorInterface
{
    public function __construct(
        private OrderRowFormatter $formatter,
    ) {
    }

    public function format(): string
    {
        return 'xlsx';
    }

    public function generate(iterable $orders): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Заказы');

        $headers = $this->formatter->headers();
        foreach ($headers as $i => $header) {
            $cell = [$i + 1, 1];
            $sheet->setCellValue($cell, $header);
            $style = $sheet->getStyleByColumnAndRow($i + 1, 1);
            $style->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $style->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF000000');
        }

        $rowIndex = 2;
        foreach ($this->formatter->rows($orders) as $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex, $value);
            }
            ++$rowIndex;
        }

        foreach (range(1, count($headers)) as $columnIndex) {
            $sheet->getColumnDimensionByColumn($columnIndex)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $body = (string) ob_get_clean();

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $response = new Response($body);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="orders.xlsx"');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }
}

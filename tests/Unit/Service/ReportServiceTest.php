<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\Report\ReportGeneratorInterface;
use App\Service\Report\ReportService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ReportServiceTest extends TestCase
{
    public function testDispatchesToTheGeneratorMatchingTheRequestedFormat(): void
    {
        $expected = new Response('csv-body');
        $csv = $this->makeGenerator('csv', $expected);
        $pdf = $this->makeGenerator('pdf');

        $service = new ReportService([$csv, $pdf]);

        self::assertSame($expected, $service->generate('csv', []));
    }

    public function testListsAllSupportedFormats(): void
    {
        $service = new ReportService([
            $this->makeGenerator('csv'),
            $this->makeGenerator('pdf'),
            $this->makeGenerator('xlsx'),
        ]);

        self::assertSame(['csv', 'pdf', 'xlsx'], $service->supportedFormats());
    }

    public function testUnknownFormatRaisesNotFound(): void
    {
        $service = new ReportService([$this->makeGenerator('csv')]);

        $this->expectException(NotFoundHttpException::class);
        $service->generate('docx', []);
    }

    private function makeGenerator(string $format, ?Response $response = null): ReportGeneratorInterface
    {
        $mock = $this->createMock(ReportGeneratorInterface::class);
        $mock->method('format')->willReturn($format);
        $mock->method('generate')->willReturn($response ?? new Response());

        return $mock;
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Report\CsvReportGenerator;
use App\Service\Report\OrderRowFormatter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class CsvReportGeneratorTest extends TestCase
{
    public function testFormatIdentifierIsCsv(): void
    {
        self::assertSame('csv', (new CsvReportGenerator(new OrderRowFormatter()))->format());
    }

    public function testGenerateProducesAStreamedResponseWithCsvHeaders(): void
    {
        $generator = new CsvReportGenerator(new OrderRowFormatter());

        $response = $generator->generate([$this->makeOrder()]);

        self::assertInstanceOf(StreamedResponse::class, $response);
        self::assertSame('text/csv; charset=utf-8', $response->headers->get('Content-Type'));
        self::assertStringContainsString('orders.csv', (string) $response->headers->get('Content-Disposition'));

        ob_start();
        $response->sendContent();
        $body = (string) ob_get_clean();

        self::assertStringStartsWith("\xEF\xBB\xBF", $body, 'BOM expected so Excel reads UTF-8.');
        self::assertStringContainsString('Bag', $body);
        self::assertStringContainsString('Alice Liddell', $body);
    }

    private function makeOrder(): Order
    {
        $order = (new Order())
            ->setFirstName('Alice')->setLastName('Liddell')
            ->setPhone('+7000')->setCity('Москва')->setAddress('Тверская, 1')
            ->setDelivery('Курьер')->setPayment('Картой онлайн')
            ->setTotalSum(500);
        $order->addItem((new OrderItem())->setCategory('Bag')->setSize('M')->setQuantity(2));

        return $order;
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class ReportControllerTest extends AbstractWebTestCase
{
    public function testCsvReportIsAttachedAsUtf8File(): void
    {
        $user = $this->seedUserWithOrder();
        $this->loginAs($user);

        $this->client->request('GET', '/reports/csv');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'text/csv; charset=utf-8');
        self::assertStringContainsString('orders.csv', (string) $this->client->getResponse()->headers->get('Content-Disposition'));

        $body = $this->client->getInternalResponse()->getContent();
        self::assertStringStartsWith("\xEF\xBB\xBF", $body);
        self::assertStringContainsString('Bag', $body);
    }

    public function testXlsxReportIsServedAsSpreadsheet(): void
    {
        $user = $this->seedUserWithOrder();
        $this->loginAs($user);

        $this->client->request('GET', '/reports/xlsx');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );
    }

    public function testPdfReportIsServedAsPdf(): void
    {
        $user = $this->seedUserWithOrder();
        $this->loginAs($user);

        $this->client->request('GET', '/reports/pdf');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/pdf');
        self::assertStringStartsWith('%PDF-', $this->client->getInternalResponse()->getContent());
    }

    public function testUnknownFormatYields404(): void
    {
        $this->loginAs($this->createUser());

        $this->client->request('GET', '/reports/docx');

        self::assertResponseStatusCodeSame(404);
    }

    public function testAnonymousUserCannotDownloadReports(): void
    {
        $this->client->request('GET', '/reports/csv');

        self::assertResponseRedirects('/login');
    }

    private function seedUserWithOrder(): User
    {
        $user = $this->createUser('alice');

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $order = (new Order())
            ->setUser($user)
            ->setFirstName('Alice')->setLastName('Liddell')
            ->setPhone('+70000000000')->setCity('Москва')->setAddress('Тверская, 1')
            ->setDelivery('Курьер')->setPayment('Картой онлайн')
            ->setTotalSum(500);

        $order->addItem((new OrderItem())->setCategory('Bag')->setSize('M')->setQuantity(2));

        $em->persist($order);
        $em->flush();

        return $user;
    }
}

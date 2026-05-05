<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\OrderRepository;

final class OrderControllerTest extends AbstractWebTestCase
{
    public function testHomePageRendersCatalogForLoggedInUser(): void
    {
        $user = $this->createUser();
        $this->loginAs($user);

        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Магазин');
        self::assertSelectorTextContains('h2', 'Сумки');
    }

    public function testValidOrderSubmissionPersistsOrderAndRedirects(): void
    {
        $user = $this->createUser();
        $this->loginAs($user);

        $crawler = $this->client->request('GET', '/');
        $form = $crawler->selectButton('Оформить заказ')->form();
        $values = $form->getPhpValues();

        $values['order_form']['firstName'] = 'Alice';
        $values['order_form']['lastName'] = 'Liddell';
        $values['order_form']['phone'] = '+70000000000';
        $values['order_form']['city'] = 'Москва';
        $values['order_form']['address'] = 'Тверская, 1';
        $values['order_form']['delivery'] = 'Курьер';
        $values['order_form']['payment'] = 'Картой онлайн';
        $values['order_form']['totalSum'] = '2592000';
        $values['order_form']['items'] = [
            ['category' => 'Сумка Hermes Birkin 30 Black Togo PHW', 'size' => '30', 'quantity' => 1],
        ];

        $this->client->request($form->getMethod(), $form->getUri(), $values);

        self::assertResponseRedirects('/');

        /** @var OrderRepository $orders */
        $orders = self::getContainer()->get(OrderRepository::class);
        $found = $orders->findAllForUser($user);
        self::assertCount(1, $found);
        self::assertSame(2_592_000, $found[0]->getTotalSum());
        self::assertCount(1, $found[0]->getItems());
    }

    public function testRegularUserSeesOnlyTheirOwnOrders(): void
    {
        $user = $this->createUser('alice');
        $other = $this->createUser('bob', 'pw', [], 'bob@example.com');

        $this->seedOrder($user, 'Alice');
        $this->seedOrder($other, 'Bob');

        $this->loginAs($user);
        $this->client->request('GET', '/');

        self::assertSelectorTextContains('body', 'Alice');
        self::assertSelectorTextNotContains('body', 'Bob');
    }

    public function testAdminSeesEveryOrder(): void
    {
        $admin = $this->createUser('admin', 'pw', [User::ROLE_ADMIN]);
        $user = $this->createUser('bob', 'pw', [], 'bob@example.com');
        $this->seedOrder($user, 'Bob');

        $this->loginAs($admin);
        $this->client->request('GET', '/');

        self::assertSelectorTextContains('body', 'Bob');
    }

    public function testInvalidOrderShowsValidationErrors(): void
    {
        $user = $this->createUser();
        $this->loginAs($user);

        $crawler = $this->client->request('GET', '/');
        $form = $crawler->selectButton('Оформить заказ')->form();
        $values = $form->getPhpValues();

        // Empty payload — every required field is missing, no items.
        $values['order_form']['items'] = [];

        $this->client->request($form->getMethod(), $form->getUri(), $values);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.errors', 'Исправьте ошибки');
    }

    private function seedOrder(User $user, string $firstName): void
    {
        $crawler = $this->client->request('GET', '/');
        unset($crawler);
        $em = self::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);

        $order = new \App\Entity\Order();
        $order
            ->setUser($user)->setFirstName($firstName)->setLastName('Test')
            ->setPhone('+70000000000')->setCity('Москва')->setAddress('Адрес')
            ->setDelivery('Курьер')->setPayment('Картой онлайн')
            ->setTotalSum(100);
        $item = (new \App\Entity\OrderItem())->setCategory('Bag')->setSize('M')->setQuantity(1);
        $order->addItem($item);
        $em->persist($order);
        $em->flush();
    }
}

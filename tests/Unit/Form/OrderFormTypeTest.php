<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Dto\OrderInput;
use App\Form\OrderFormType;
use Symfony\Component\Form\Test\TypeTestCase;

final class OrderFormTypeTest extends TypeTestCase
{
    public function testSubmitValidDataMapsOntoOrderInput(): void
    {
        $formData = [
            'firstName' => 'Alice',
            'lastName' => 'Liddell',
            'phone' => '+7 (000) 000-00-00',
            'city' => 'Москва',
            'address' => 'Тверская, 1',
            'delivery' => 'Курьер',
            'payment' => 'Картой онлайн',
            'totalSum' => '500',
            'items' => [
                ['category' => 'Bag', 'size' => 'M', 'quantity' => 2],
            ],
        ];

        $form = $this->factory->create(OrderFormType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized(), (string) $form->getTransformationFailure()?->getMessage());

        /** @var OrderInput $data */
        $data = $form->getData();
        self::assertInstanceOf(OrderInput::class, $data);
        self::assertSame('Alice', $data->firstName);
        self::assertSame('Москва', $data->city);
        self::assertSame(500, $data->totalSum);
        self::assertCount(1, $data->items);
        self::assertSame('Bag', $data->items[0]->category);
        self::assertSame(2, $data->items[0]->quantity);
    }

    protected function getTypeExtensions(): array
    {
        return [];
    }
}

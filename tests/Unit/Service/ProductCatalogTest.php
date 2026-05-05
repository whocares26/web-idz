<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\ProductCatalog;
use PHPUnit\Framework\TestCase;

final class ProductCatalogTest extends TestCase
{
    public function testAllReturnsTheFullCatalog(): void
    {
        $catalog = new ProductCatalog();

        self::assertCount(8, $catalog->all());
    }

    public function testCatalogIsCachedAfterFirstCall(): void
    {
        $catalog = new ProductCatalog();

        self::assertSame($catalog->all(), $catalog->all());
    }

    public function testByCategorySegmentsTheCatalog(): void
    {
        $catalog = new ProductCatalog();

        $bags = $catalog->byCategory(ProductCatalog::CATEGORY_BAGS);
        $clothing = $catalog->byCategory(ProductCatalog::CATEGORY_CLOTHING);

        self::assertCount(4, $bags);
        self::assertCount(4, $clothing);
        foreach ($bags as $product) {
            self::assertSame(ProductCatalog::CATEGORY_BAGS, $product->category);
        }
    }

    public function testByCategoryReturnsEmptyArrayForUnknownCategory(): void
    {
        $catalog = new ProductCatalog();

        self::assertSame([], $catalog->byCategory('Unknown'));
    }

    public function testFindReturnsAProductByIdOrNull(): void
    {
        $catalog = new ProductCatalog();

        self::assertSame(1, $catalog->find(1)?->id);
        self::assertNull($catalog->find(9999));
    }
}

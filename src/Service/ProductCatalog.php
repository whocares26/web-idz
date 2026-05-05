<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Product;

/**
 * Static catalog of products.
 *
 * Lives in the service layer so it can be injected, mocked and replaced
 * later by a database-backed implementation without touching the controllers.
 */
final class ProductCatalog
{
    public const CATEGORY_BAGS = 'Сумки';
    public const CATEGORY_CLOTHING = 'Одежда';

    /**
     * @var list<Product>|null
     */
    private ?array $cache = null;

    /**
     * @return list<Product>
     */
    public function all(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        return $this->cache = [
            new Product(1, 'Сумка Hermes Birkin 25 Havane GHW',           'images/item1.png', 9_153_000, 113_000, 97_372, ['25'], self::CATEGORY_BAGS),
            new Product(2, 'Сумка Hermes Birkin Cargo 25 Vert Moyen PHW', 'images/item2.png', 3_928_500,  48_500, 41_792, ['25'], self::CATEGORY_BAGS),
            new Product(3, 'Сумка Hermes Birkin 30 HSS Biscuit/Craie GHW','images/item3.png', 2_592_000,  32_000, 27_574, ['30'], self::CATEGORY_BAGS),
            new Product(4, 'Сумка Hermes Birkin 30 Black Togo PHW',       'images/item4.png', 2_592_000,  32_000, 27_574, ['30'], self::CATEGORY_BAGS),
            new Product(5, 'Hermes Пиджак из шерсти и шелка',             'images/item5.png',   441_600,   4_800,  4_420, ['XS', 'S', 'M', 'L', 'XL', 'XXL'], self::CATEGORY_CLOTHING),
            new Product(6, 'Hermes Двусторонняя кожаная куртка',          'images/item6.png', 1_702_000,  18_500, 17_040, ['XS', 'S', 'M', 'L', 'XL', 'XXL'], self::CATEGORY_CLOTHING),
            new Product(7, 'Hermes Кожаная куртка',                       'images/item7.png', 1_104_000,  12_000, 11_050, ['XS', 'S', 'M', 'L', 'XL', 'XXL'], self::CATEGORY_CLOTHING),
            new Product(8, 'Zilly Бомбер из кожи страуса',                'images/item8.png', 2_300_000,  25_000, 23_020, ['XS', 'S', 'M', 'L', 'XL', 'XXL'], self::CATEGORY_CLOTHING),
        ];
    }

    /**
     * @return list<Product>
     */
    public function byCategory(string $category): array
    {
        return array_values(array_filter($this->all(), static fn (Product $p) => $p->category === $category));
    }

    public function find(int $id): ?Product
    {
        foreach ($this->all() as $product) {
            if ($product->id === $id) {
                return $product;
            }
        }

        return null;
    }
}

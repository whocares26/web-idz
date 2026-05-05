<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * Immutable value object describing a product in the catalog.
 */
final readonly class Product
{
    /**
     * @param list<string> $sizes
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $image,
        public int $priceRub,
        public int $priceUsd,
        public int $priceEur,
        public array $sizes,
        public string $category,
    ) {
    }
}

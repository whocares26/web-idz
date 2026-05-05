<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\Report\Transliterator;
use PHPUnit\Framework\TestCase;

final class TransliteratorTest extends TestCase
{
    public function testRussianTextIsTransliteratedToAscii(): void
    {
        $transliterator = new Transliterator();

        self::assertSame('Privet, mir!', $transliterator->transliterate('Привет, мир!'));
    }

    public function testRubleSignBecomesRub(): void
    {
        self::assertSame('1000 RUB', (new Transliterator())->transliterate('1000 ₽'));
    }

    public function testNonCyrillicTextIsLeftIntact(): void
    {
        self::assertSame('Hello world 42', (new Transliterator())->transliterate('Hello world 42'));
    }

    public function testHardAndSoftSignsAreDropped(): void
    {
        self::assertSame('obekt', (new Transliterator())->transliterate('объект'));
    }
}

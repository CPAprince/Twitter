<?php

declare(strict_types=1);

namespace Twitter\Tests\Tweet\Application\UseCase\Shared;

use Assert\LazyAssertionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twitter\Tweet\Application\UseCase\Shared\PaginationMeta;

#[Group('unit')]
#[CoversClass(PaginationMeta::class)]
final class PaginationMetaTest extends TestCase
{
    #[Test]
    #[DataProvider('validDataProvider')]
    public function calculatesTotalPagesCorrectly(int $totalItems, int $limit, int $expectedPages): void
    {
        $meta = new PaginationMeta(page: 1, limit: $limit, totalItems: $totalItems);

        self::assertSame($expectedPages, $meta->totalPages);
    }

    public static function validDataProvider(): iterable
    {
        yield 'zero items' => [0, 20, 0];
        yield 'exact page count' => [100, 20, 5];
        yield 'rounding up' => [101, 20, 6];
        yield 'limit equals total' => [10, 10, 1];
        yield 'limit greater than total' => [5, 10, 1];
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function throwsExceptionOnInvalidData(int $page, int $limit, int $totalItems): void
    {
        $this->expectException(LazyAssertionException::class);

        new PaginationMeta($page, $limit, $totalItems);
    }

    public static function invalidDataProvider(): iterable
    {
        yield 'page less than 1' => [0, 20, 100];
        yield 'limit less than 1' => [1, 0, 100];
        yield 'total items negative' => [1, 20, -1];
    }
}
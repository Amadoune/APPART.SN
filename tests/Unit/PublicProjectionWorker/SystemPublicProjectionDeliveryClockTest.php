<?php

namespace Tests\Unit\PublicProjectionWorker;

use App\Infrastructure\PublicProjectionWorker\SystemPublicProjectionDeliveryClock;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SystemPublicProjectionDeliveryClockTest extends TestCase
{
    public function test_it_returns_the_current_immutable_system_time(): void
    {
        $before = new DateTimeImmutable('now');
        $actual = (new SystemPublicProjectionDeliveryClock)->now();
        $after = new DateTimeImmutable('now');

        self::assertGreaterThanOrEqual($before, $actual);
        self::assertLessThanOrEqual($after, $actual);
    }
}

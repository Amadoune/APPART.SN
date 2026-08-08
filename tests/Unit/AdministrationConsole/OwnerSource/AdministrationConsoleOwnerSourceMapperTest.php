<?php

namespace Tests\Unit\AdministrationConsole\OwnerSource;

use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationOperatorRevisionState;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationQueueRevisionState;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueStatusV1;
use Appart\Modules\AdministrationConsole\Infrastructure\Persistence\AdministrationConsoleOwnerSourceMapper;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AdministrationConsoleOwnerSourceMapperTest extends TestCase
{
    #[Test]
    public function it_maps_canonically_and_rejects_a_divergent_checksum(): void
    {
        $mapper = new AdministrationConsoleOwnerSourceMapper;
        $state = new AdministrationOperatorRevisionState('console:42', 1, AdministrationOperatorStatusV1::Available, new DateTimeImmutable('2026-08-03T10:00:00.123456+02:00'), new DateTimeImmutable('2026-08-03T10:00:01.123456+02:00'));
        $row = $mapper->operatorToRow($state);
        self::assertSame('2026-08-03T08:00:00.123456Z', $row['effective_at']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $row['revision_checksum']);
        self::assertSame(AdministrationOperatorStatusV1::Available, $mapper->toOperatorState($row)->decision);
        self::assertSame('queue', $mapper->queueToRow(new AdministrationQueueRevisionState('console:42', 1, AdministrationQueueStatusV1::Ready, $state->effectiveAt, $state->recordedAt))['stream_type']);
        $row['decision'] = 'unavailable';
        $this->expectException(RuntimeException::class);
        $mapper->toOperatorState($row);
    }
}

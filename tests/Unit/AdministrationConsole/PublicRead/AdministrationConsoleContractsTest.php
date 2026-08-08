<?php

namespace Tests\Unit\AdministrationConsole\PublicRead;

use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AdministrationConsoleContractsTest extends TestCase
{
    public function test_catalogues_are_closed_and_results_expose_only_status(): void
    {
        self::assertSame(['available', 'unavailable', 'missing', 'corrupted', 'dependency_unavailable'], array_column(AdministrationOperatorStatusV1::cases(), 'value'));
        self::assertSame(['ready', 'empty', 'missing', 'corrupted', 'dependency_unavailable'], array_column(AdministrationQueueStatusV1::cases(), 'value'));
        self::assertSame(['available', 'missing', 'corrupted', 'dependency_unavailable'], array_column(AdministrationAuditStatusV1::cases(), 'value'));
        self::assertSame(['status'], array_keys(get_object_vars(new AdministrationOperatorResultV1(AdministrationOperatorStatusV1::Available))));
        self::assertSame(['status'], array_keys(get_object_vars(new AdministrationQueueResultV1(AdministrationQueueStatusV1::Ready))));
        self::assertSame(['status'], array_keys(get_object_vars(new AdministrationAuditResultV1(AdministrationAuditStatusV1::Available))));
    }

    public function test_value_objects_are_canonical_and_observation_is_utc(): void
    {
        self::assertSame('operator:42', (new AdministrationSubjectKey('operator:42'))->canonical());
        self::assertSame('2026-08-02T12:00:00.123456Z', (new AdministrationObservedAt(new DateTimeImmutable('2026-08-02T14:00:00.123456+02:00')))->canonical());
    }

    public function test_subject_key_rejects_non_canonical_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AdministrationSubjectKey(' operator:42 ');
    }
}

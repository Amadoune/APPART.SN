<?php

namespace Tests\Unit\AdministrationConsole\Event;

use Appart\Modules\AdministrationConsole\Application\Event\AdministrationAuditEventFactory;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationAuditEventStatus;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationAuditEventType;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationOperatorEventFactory;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationOperatorEventStatus;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationOperatorEventType;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationQueueEventFactory;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationQueueEventStatus;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationQueueEventType;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationAuditReaderV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationOperatorReaderV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationQueueReaderV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdministrationConsoleEventFactoryTest extends TestCase
{
    #[DataProvider('operatorCases')]
    public function test_every_operator_result_produces_exactly_one_event(AdministrationOperatorStatusV1 $public, AdministrationOperatorEventStatus $eventStatus): void
    {
        $reader = $this->createMock(AdministrationOperatorReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn(new AdministrationOperatorResultV1($public));

        $event = (new AdministrationOperatorEventFactory($reader))->create(self::subject(), self::observedAt());

        self::assertSame(AdministrationOperatorEventType::Observed, $event->type);
        self::assertSame(['status' => $eventStatus->value, 'observedAt' => '2026-08-03T12:00:00.123456Z'], $event->payload->canonical());
    }

    #[DataProvider('queueCases')]
    public function test_every_queue_result_produces_exactly_one_event(AdministrationQueueStatusV1 $public, AdministrationQueueEventStatus $eventStatus): void
    {
        $reader = $this->createMock(AdministrationQueueReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn(new AdministrationQueueResultV1($public));

        $event = (new AdministrationQueueEventFactory($reader))->create(self::subject(), self::observedAt());

        self::assertSame(AdministrationQueueEventType::Observed, $event->type);
        self::assertSame(['status' => $eventStatus->value, 'observedAt' => '2026-08-03T12:00:00.123456Z'], $event->payload->canonical());
    }

    #[DataProvider('auditCases')]
    public function test_every_audit_result_produces_exactly_one_event(AdministrationAuditStatusV1 $public, AdministrationAuditEventStatus $eventStatus): void
    {
        $reader = $this->createMock(AdministrationAuditReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn(new AdministrationAuditResultV1($public));

        $event = (new AdministrationAuditEventFactory($reader))->create(self::subject(), self::observedAt());

        self::assertSame(AdministrationAuditEventType::Observed, $event->type);
        self::assertSame(['status' => $eventStatus->value, 'observedAt' => '2026-08-03T12:00:00.123456Z'], $event->payload->canonical());
    }

    /** @return iterable<string, array{AdministrationOperatorStatusV1, AdministrationOperatorEventStatus}> */
    public static function operatorCases(): iterable
    {
        foreach (AdministrationOperatorStatusV1::cases() as $status) {
            yield $status->value => [$status, AdministrationOperatorEventStatus::from($status->value)];
        }
    }

    /** @return iterable<string, array{AdministrationQueueStatusV1, AdministrationQueueEventStatus}> */
    public static function queueCases(): iterable
    {
        foreach (AdministrationQueueStatusV1::cases() as $status) {
            yield $status->value => [$status, AdministrationQueueEventStatus::from($status->value)];
        }
    }

    /** @return iterable<string, array{AdministrationAuditStatusV1, AdministrationAuditEventStatus}> */
    public static function auditCases(): iterable
    {
        foreach (AdministrationAuditStatusV1::cases() as $status) {
            yield $status->value => [$status, AdministrationAuditEventStatus::from($status->value)];
        }
    }

    private static function subject(): AdministrationSubjectKey
    {
        return new AdministrationSubjectKey('administration:42');
    }

    private static function observedAt(): AdministrationObservedAt
    {
        return new AdministrationObservedAt(new DateTimeImmutable('2026-08-03T12:00:00.123456Z'));
    }
}

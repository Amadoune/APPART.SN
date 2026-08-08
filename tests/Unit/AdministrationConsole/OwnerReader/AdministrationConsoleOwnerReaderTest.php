<?php

namespace Tests\Unit\AdministrationConsole\OwnerReader;

use Appart\Modules\AdministrationConsole\Application\OwnerReader\AdministrationAuditOwnerReader;
use Appart\Modules\AdministrationConsole\Application\OwnerReader\AdministrationConsoleOwnerReaderPolicy;
use Appart\Modules\AdministrationConsole\Application\OwnerReader\AdministrationOperatorOwnerReader;
use Appart\Modules\AdministrationConsole\Application\OwnerReader\AdministrationQueueOwnerReader;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationAuditReadResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationAuditRevisionState;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationConsoleOwnerSource;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationOperatorReadResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationOperatorRevisionState;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationQueueReadResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationQueueRevisionState;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdministrationConsoleOwnerReaderTest extends TestCase
{
    #[DataProvider('operatorCases')]
    public function test_operator_reduction_is_mechanical(AdministrationOperatorReadResult $owner, AdministrationOperatorStatusV1 $expected): void
    {
        $source = $this->createMock(AdministrationConsoleOwnerSource::class);
        $source->method('readOperator')->willReturn($owner);

        $result = (new AdministrationOperatorOwnerReader($source, new AdministrationConsoleOwnerReaderPolicy))->read($this->subject(), $this->observedAt());

        self::assertSame($expected, $result->status);
    }

    #[DataProvider('queueCases')]
    public function test_queue_reduction_is_mechanical(AdministrationQueueReadResult $owner, AdministrationQueueStatusV1 $expected): void
    {
        $source = $this->createMock(AdministrationConsoleOwnerSource::class);
        $source->method('readQueue')->willReturn($owner);

        $result = (new AdministrationQueueOwnerReader($source, new AdministrationConsoleOwnerReaderPolicy))->read($this->subject(), $this->observedAt());

        self::assertSame($expected, $result->status);
    }

    #[DataProvider('auditCases')]
    public function test_audit_reduction_is_mechanical(AdministrationAuditReadResult $owner, AdministrationAuditStatusV1 $expected): void
    {
        $source = $this->createMock(AdministrationConsoleOwnerSource::class);
        $source->method('readAudit')->willReturn($owner);

        $result = (new AdministrationAuditOwnerReader($source, new AdministrationConsoleOwnerReaderPolicy))->read($this->subject(), $this->observedAt());

        self::assertSame($expected, $result->status);
    }

    /** @return iterable<string, array{AdministrationOperatorReadResult, AdministrationOperatorStatusV1}> */
    public static function operatorCases(): iterable
    {
        foreach ([AdministrationOperatorStatusV1::Available, AdministrationOperatorStatusV1::Unavailable] as $status) {
            $revision = new AdministrationOperatorRevisionState('administration:1', 1, $status, self::effectiveAt(), self::recordedAt());
            yield $status->value => [AdministrationOperatorReadResult::found($revision), $status];
        }
        yield 'missing' => [AdministrationOperatorReadResult::missing(), AdministrationOperatorStatusV1::Missing];
        yield 'corrupted' => [AdministrationOperatorReadResult::corrupted(), AdministrationOperatorStatusV1::Corrupted];
        yield 'dependency_unavailable' => [AdministrationOperatorReadResult::dependencyUnavailable(), AdministrationOperatorStatusV1::DependencyUnavailable];
    }

    /** @return iterable<string, array{AdministrationQueueReadResult, AdministrationQueueStatusV1}> */
    public static function queueCases(): iterable
    {
        foreach ([AdministrationQueueStatusV1::Ready, AdministrationQueueStatusV1::Empty] as $status) {
            $revision = new AdministrationQueueRevisionState('administration:1', 1, $status, self::effectiveAt(), self::recordedAt());
            yield $status->value => [AdministrationQueueReadResult::found($revision), $status];
        }
        yield 'missing' => [AdministrationQueueReadResult::missing(), AdministrationQueueStatusV1::Missing];
        yield 'corrupted' => [AdministrationQueueReadResult::corrupted(), AdministrationQueueStatusV1::Corrupted];
        yield 'dependency_unavailable' => [AdministrationQueueReadResult::dependencyUnavailable(), AdministrationQueueStatusV1::DependencyUnavailable];
    }

    /** @return iterable<string, array{AdministrationAuditReadResult, AdministrationAuditStatusV1}> */
    public static function auditCases(): iterable
    {
        $revision = new AdministrationAuditRevisionState('administration:1', 1, AdministrationAuditStatusV1::Available, self::effectiveAt(), self::recordedAt());
        yield 'available' => [AdministrationAuditReadResult::found($revision), AdministrationAuditStatusV1::Available];
        yield 'missing' => [AdministrationAuditReadResult::missing(), AdministrationAuditStatusV1::Missing];
        yield 'corrupted' => [AdministrationAuditReadResult::corrupted(), AdministrationAuditStatusV1::Corrupted];
        yield 'dependency_unavailable' => [AdministrationAuditReadResult::dependencyUnavailable(), AdministrationAuditStatusV1::DependencyUnavailable];
    }

    private function subject(): AdministrationSubjectKey
    {
        return new AdministrationSubjectKey('administration:1');
    }

    private function observedAt(): AdministrationObservedAt
    {
        return new AdministrationObservedAt(new DateTimeImmutable('2026-08-03T12:00:00Z'));
    }

    private static function effectiveAt(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-03T10:00:00Z');
    }

    private static function recordedAt(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-03T10:00:01Z');
    }
}

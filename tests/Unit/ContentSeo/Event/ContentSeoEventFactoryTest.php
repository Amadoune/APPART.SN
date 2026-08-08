<?php

namespace Tests\Unit\ContentSeo\Event;

use Appart\Modules\ContentSeo\Application\Event\EditorialContentEventFactory;
use Appart\Modules\ContentSeo\Application\Event\EditorialContentEventStatus;
use Appart\Modules\ContentSeo\Application\Event\EditorialContentEventType;
use Appart\Modules\ContentSeo\Application\Event\OperationalSeoEventFactory;
use Appart\Modules\ContentSeo\Application\Event\OperationalSeoEventStatus;
use Appart\Modules\ContentSeo\Application\Event\OperationalSeoEventType;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\EditorialContentReaderV1;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\OperationalSeoReaderV1;
use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentResultV1;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoResultV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContentSeoEventFactoryTest extends TestCase
{
    #[DataProvider('editorialCases')]
    public function test_every_editorial_result_produces_exactly_one_event(EditorialContentResultV1 $result, EditorialContentEventStatus $status): void
    {
        $reader = $this->createMock(EditorialContentReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn($result);
        $event = (new EditorialContentEventFactory($reader))->create(self::resource(), self::observedAt());

        self::assertSame(EditorialContentEventType::Observed, $event->type);
        self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-02T12:00:00.123456Z'], $event->payload->canonical());
    }

    /** @return iterable<string, array{EditorialContentResultV1, EditorialContentEventStatus}> */
    public static function editorialCases(): iterable
    {
        yield 'published' => [EditorialContentResultV1::published(), EditorialContentEventStatus::Published];
        yield 'unpublished' => [EditorialContentResultV1::unpublished(), EditorialContentEventStatus::Unpublished];
        yield 'missing' => [EditorialContentResultV1::missing(), EditorialContentEventStatus::Missing];
        yield 'corrupted' => [EditorialContentResultV1::corrupted(), EditorialContentEventStatus::Corrupted];
        yield 'dependency unavailable' => [EditorialContentResultV1::dependencyUnavailable(), EditorialContentEventStatus::DependencyUnavailable];
    }

    #[DataProvider('operationalCases')]
    public function test_every_operational_result_produces_exactly_one_event(OperationalSeoResultV1 $result, OperationalSeoEventStatus $status): void
    {
        $reader = $this->createMock(OperationalSeoReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn($result);
        $event = (new OperationalSeoEventFactory($reader))->create(self::resource(), self::observedAt());

        self::assertSame(OperationalSeoEventType::Observed, $event->type);
        self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-02T12:00:00.123456Z'], $event->payload->canonical());
    }

    /** @return iterable<string, array{OperationalSeoResultV1, OperationalSeoEventStatus}> */
    public static function operationalCases(): iterable
    {
        yield 'indexable' => [OperationalSeoResultV1::indexable(), OperationalSeoEventStatus::Indexable];
        yield 'no index' => [OperationalSeoResultV1::noIndex(), OperationalSeoEventStatus::NoIndex];
        yield 'missing' => [OperationalSeoResultV1::missing(), OperationalSeoEventStatus::Missing];
        yield 'corrupted' => [OperationalSeoResultV1::corrupted(), OperationalSeoEventStatus::Corrupted];
        yield 'dependency unavailable' => [OperationalSeoResultV1::dependencyUnavailable(), OperationalSeoEventStatus::DependencyUnavailable];
    }

    private static function resource(): ContentSeoPublicResourceKey
    {
        return new ContentSeoPublicResourceKey('page:42');
    }

    private static function observedAt(): ContentSeoObservedAt
    {
        return new ContentSeoObservedAt(new DateTimeImmutable('2026-08-02T12:00:00.123456Z'));
    }
}

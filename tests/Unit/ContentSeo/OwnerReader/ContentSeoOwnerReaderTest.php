<?php

namespace Tests\Unit\ContentSeo\OwnerReader;

use Appart\Modules\ContentSeo\Application\OwnerReader\ContentSeoOwnerReaderPolicy;
use Appart\Modules\ContentSeo\Application\OwnerReader\ContentSeoOwnerReaderStatus;
use Appart\Modules\ContentSeo\Application\OwnerReader\EditorialContentOwnerReader;
use Appart\Modules\ContentSeo\Application\OwnerReader\OperationalSeoOwnerReader;
use Appart\Modules\ContentSeo\Application\OwnerSource\ContentSeoOwnerSource;
use Appart\Modules\ContentSeo\Application\OwnerSource\EditorialContentReadResult;
use Appart\Modules\ContentSeo\Application\OwnerSource\EditorialContentRevisionState;
use Appart\Modules\ContentSeo\Application\OwnerSource\OperationalSeoReadResult;
use Appart\Modules\ContentSeo\Application\OwnerSource\OperationalSeoRevisionState;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentStatusV1;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoStatusV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContentSeoOwnerReaderTest extends TestCase
{
    #[DataProvider('editorialCases')]
    public function test_editorial_policy_is_mechanical(EditorialContentReadResult $owner, ContentSeoOwnerReaderStatus $expected): void
    {
        self::assertSame($expected, (new ContentSeoOwnerReaderPolicy)->editorial($owner)->status);
    }

    /** @return iterable<string, array{EditorialContentReadResult, ContentSeoOwnerReaderStatus}> */
    public static function editorialCases(): iterable
    {
        yield 'published' => [EditorialContentReadResult::found(self::editorialRevision(EditorialContentStatusV1::Published)), ContentSeoOwnerReaderStatus::Published];
        yield 'unpublished' => [EditorialContentReadResult::found(self::editorialRevision(EditorialContentStatusV1::Unpublished)), ContentSeoOwnerReaderStatus::Unpublished];
        yield 'missing' => [EditorialContentReadResult::missing(), ContentSeoOwnerReaderStatus::Missing];
        yield 'corrupted' => [EditorialContentReadResult::corrupted(), ContentSeoOwnerReaderStatus::Corrupted];
        yield 'dependency unavailable' => [EditorialContentReadResult::dependencyUnavailable(), ContentSeoOwnerReaderStatus::DependencyUnavailable];
    }

    #[DataProvider('operationalCases')]
    public function test_operational_policy_is_mechanical(OperationalSeoReadResult $owner, ContentSeoOwnerReaderStatus $expected): void
    {
        self::assertSame($expected, (new ContentSeoOwnerReaderPolicy)->operational($owner)->status);
    }

    /** @return iterable<string, array{OperationalSeoReadResult, ContentSeoOwnerReaderStatus}> */
    public static function operationalCases(): iterable
    {
        yield 'indexable' => [OperationalSeoReadResult::found(self::operationalRevision(OperationalSeoStatusV1::Indexable)), ContentSeoOwnerReaderStatus::Indexable];
        yield 'no index' => [OperationalSeoReadResult::found(self::operationalRevision(OperationalSeoStatusV1::NoIndex)), ContentSeoOwnerReaderStatus::NoIndex];
        yield 'missing' => [OperationalSeoReadResult::missing(), ContentSeoOwnerReaderStatus::Missing];
        yield 'corrupted' => [OperationalSeoReadResult::corrupted(), ContentSeoOwnerReaderStatus::Corrupted];
        yield 'dependency unavailable' => [OperationalSeoReadResult::dependencyUnavailable(), ContentSeoOwnerReaderStatus::DependencyUnavailable];
    }

    public function test_public_readers_delegate_to_the_unique_source_and_expose_only_status(): void
    {
        $resource = new ContentSeoPublicResourceKey('page:42');
        $observedAt = new ContentSeoObservedAt(new DateTimeImmutable('2026-08-02T12:00:00.123456Z'));
        $source = $this->createMock(ContentSeoOwnerSource::class);
        $source->expects(self::once())->method('readEditorial')->with($resource, $observedAt)->willReturn(EditorialContentReadResult::found(self::editorialRevision(EditorialContentStatusV1::Published)));
        $source->expects(self::once())->method('readOperationalSeo')->with($resource, $observedAt)->willReturn(OperationalSeoReadResult::found(self::operationalRevision(OperationalSeoStatusV1::NoIndex)));
        $policy = new ContentSeoOwnerReaderPolicy;

        self::assertSame(EditorialContentStatusV1::Published, (new EditorialContentOwnerReader($source, $policy))->read($resource, $observedAt)->status);
        self::assertSame(OperationalSeoStatusV1::NoIndex, (new OperationalSeoOwnerReader($source, $policy))->read($resource, $observedAt)->status);
    }

    private static function editorialRevision(EditorialContentStatusV1 $status): EditorialContentRevisionState
    {
        $instant = new DateTimeImmutable('2026-08-02T12:00:00.123456Z');

        return new EditorialContentRevisionState('page:42', 1, $status, $instant, $instant);
    }

    private static function operationalRevision(OperationalSeoStatusV1 $status): OperationalSeoRevisionState
    {
        $instant = new DateTimeImmutable('2026-08-02T12:00:00.123456Z');

        return new OperationalSeoRevisionState('page:42', 1, $status, $instant, $instant);
    }
}

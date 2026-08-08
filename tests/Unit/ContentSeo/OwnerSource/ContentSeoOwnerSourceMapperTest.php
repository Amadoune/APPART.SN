<?php

namespace Tests\Unit\ContentSeo\OwnerSource;

use Appart\Modules\ContentSeo\Application\OwnerSource\EditorialContentRevisionState;
use Appart\Modules\ContentSeo\Application\OwnerSource\OperationalSeoRevisionState;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentStatusV1;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoStatusV1;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\ContentSeoOwnerSourceMapper;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ContentSeoOwnerSourceMapperTest extends TestCase
{
    #[Test]
    public function mapper_round_trips_both_owner_streams_deterministically(): void
    {
        $mapper = new ContentSeoOwnerSourceMapper;
        $resource = new ContentSeoPublicResourceKey('editorial/guides/dakar');
        $effective = new DateTimeImmutable('2026-08-02T10:00:00.123456Z');
        $recorded = new DateTimeImmutable('2026-08-02T10:00:01.123456Z');

        $editorial = new EditorialContentRevisionState($resource, 1, EditorialContentStatusV1::Published, $effective, $recorded);
        $seo = new OperationalSeoRevisionState($resource, 1, OperationalSeoStatusV1::Indexable, $effective, $recorded);

        self::assertEquals($editorial, $mapper->toEditorialState($mapper->editorialToRow($editorial)));
        self::assertEquals($seo, $mapper->toOperationalSeoState($mapper->operationalSeoToRow($seo)));
        self::assertSame($mapper->editorialToRow($editorial), $mapper->editorialToRow($editorial));
    }

    #[Test]
    public function mapper_rejects_checksum_divergence(): void
    {
        $mapper = new ContentSeoOwnerSourceMapper;
        $state = new EditorialContentRevisionState('editorial/guides/dakar', 1, EditorialContentStatusV1::Unpublished, new DateTimeImmutable('2026-08-02T10:00:00Z'), new DateTimeImmutable('2026-08-02T10:00:01Z'));
        $row = $mapper->editorialToRow($state);
        $row['decision'] = 'published';

        $this->expectException(RuntimeException::class);
        $mapper->toEditorialState($row);
    }
}

<?php

namespace Tests\Feature;

use Appart\Modules\ContentSeo\Application\OwnerReader\EditorialContentOwnerReader;
use Appart\Modules\ContentSeo\Application\OwnerReader\OperationalSeoOwnerReader;
use Appart\Modules\ContentSeo\Application\OwnerSource\ContentSeoOwnerSource;
use Appart\Modules\ContentSeo\Application\OwnerSource\EditorialContentReadResult;
use Appart\Modules\ContentSeo\Application\OwnerSource\OperationalSeoReadResult;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\EditorialContentReaderV1;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\OperationalSeoReaderV1;
use Tests\TestCase;

final class ContentSeoHttpOwnerReaderCompositionTest extends TestCase
{
    public function test_http_resolves_the_two_certified_owner_readers(): void
    {
        $source = $this->createMock(ContentSeoOwnerSource::class);
        $source->method('readEditorial')->willReturn(EditorialContentReadResult::missing());
        $source->method('readOperationalSeo')->willReturn(OperationalSeoReadResult::missing());
        $this->app->instance(ContentSeoOwnerSource::class, $source);

        self::assertInstanceOf(EditorialContentOwnerReader::class, $this->app->make(EditorialContentReaderV1::class));
        self::assertInstanceOf(OperationalSeoOwnerReader::class, $this->app->make(OperationalSeoReaderV1::class));

        $query = '?resourceKey=page%3A42&observedAt=2026-08-02T10%3A00%3A00.123456%2B00%3A00';
        $this->getJson('/api/content-seo/editorial-content'.$query)->assertNotFound()->assertExactJson(['status' => 'missing']);
        $this->getJson('/api/content-seo/operational-seo'.$query)->assertNotFound()->assertExactJson(['status' => 'missing']);
    }
}

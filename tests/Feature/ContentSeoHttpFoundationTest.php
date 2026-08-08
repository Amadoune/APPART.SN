<?php

namespace Tests\Feature;

use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\EditorialContentReaderV1;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\OperationalSeoReaderV1;
use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentResultV1;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoResultV1;
use Tests\TestCase;

final class ContentSeoHttpFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(EditorialContentReaderV1::class, new class implements EditorialContentReaderV1
        {
            public function read(ContentSeoPublicResourceKey $resource, ContentSeoObservedAt $observedAt): EditorialContentResultV1
            {
                return EditorialContentResultV1::published();
            }
        });
        $this->app->instance(OperationalSeoReaderV1::class, new class implements OperationalSeoReaderV1
        {
            public function read(ContentSeoPublicResourceKey $resource, ContentSeoObservedAt $observedAt): OperationalSeoResultV1
            {
                return OperationalSeoResultV1::indexable();
            }
        });
    }

    public function test_endpoints_pass_contract_inputs_and_return_only_status(): void
    {
        $query = '?resourceKey=page%3A42&observedAt=2026-08-02T10%3A00%3A00.123456%2B00%3A00';
        $this->getJson('/api/content-seo/editorial-content'.$query)->assertOk()->assertExactJson(['status' => 'published']);
        $this->getJson('/api/content-seo/operational-seo'.$query)->assertOk()->assertExactJson(['status' => 'indexable']);
    }

    public function test_endpoints_reject_missing_and_unknown_inputs(): void
    {
        $this->getJson('/api/content-seo/editorial-content')->assertUnprocessable();
        $this->getJson('/api/content-seo/operational-seo?resourceKey=x&observedAt=2026-08-02T10%3A00%3A00.123456%2B00%3A00&sql=forbidden')->assertUnprocessable();
    }
}

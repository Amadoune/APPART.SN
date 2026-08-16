<?php

namespace Tests\Feature;

use App\Application\PublicMediaBinaryDelivery\Contract\ResolvePublicMediaBinaryV1;
use App\Application\PublicMediaBinaryDelivery\PublicMediaBinaryResult;
use App\Application\PublicMediaBinaryDelivery\PublicMediaBinaryStatus;
use Tests\TestCase;

final class PublicMediaBinaryDeliveryHttpTest extends TestCase
{
    public const MEDIA = '1586b2bc-48ab-57b7-948a-9d9a19813ca8';

    public function test_guest_get_streams_exact_ready_revision_with_public_headers(): void
    {
        $this->app->instance(ResolvePublicMediaBinaryV1::class, new class implements ResolvePublicMediaBinaryV1
        {
            public function resolve(string $mediaId, int $assetVersion): PublicMediaBinaryResult
            {
                TestCase::assertSame(PublicMediaBinaryDeliveryHttpTest::MEDIA, $mediaId);
                TestCase::assertSame(2, $assetVersion);
                $stream = fopen('php://temp', 'w+b');
                fwrite($stream, 'binary-proof');
                rewind($stream);

                return new PublicMediaBinaryResult(PublicMediaBinaryStatus::Found, $stream, 'image/jpeg', 12, hash('sha256', 'binary-proof'));
            }
        });

        $response = $this->get('/media/'.self::MEDIA.'/revisions/2');

        $response->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        self::assertSame('binary-proof', $response->streamedContent());
        self::assertStringNotContainsString('owners/', (string) $response->getContent());
    }

    public function test_ineligible_and_dependency_results_have_closed_http_reductions(): void
    {
        $this->app->instance(ResolvePublicMediaBinaryV1::class, new class implements ResolvePublicMediaBinaryV1
        {
            public function resolve(string $mediaId, int $assetVersion): PublicMediaBinaryResult
            {
                return new PublicMediaBinaryResult($assetVersion === 2 ? PublicMediaBinaryStatus::NotFound : PublicMediaBinaryStatus::DependencyUnavailable);
            }
        });

        $this->get('/media/'.self::MEDIA.'/revisions/2')->assertNotFound()->assertContent('');
        $this->get('/media/'.self::MEDIA.'/revisions/3')->assertStatus(503)->assertContent('');
    }

    public function test_malformed_and_traversal_locators_are_rejected_before_resolution(): void
    {
        $this->app->instance(ResolvePublicMediaBinaryV1::class, new class implements ResolvePublicMediaBinaryV1
        {
            public function resolve(string $mediaId, int $assetVersion): PublicMediaBinaryResult
            {
                TestCase::fail('Malformed routes must not reach the resolver.');
            }
        });

        $this->get('/media/not-a-uuid/revisions/2')->assertNotFound();
        $this->get('/media/..%2F..%2Fsecret/revisions/2')->assertNotFound();
        $this->get('/media/'.self::MEDIA.'/revisions/0')->assertNotFound();
        $this->get('/media/'.self::MEDIA.'/revisions/2%2Fsecret')->assertNotFound();
    }
}

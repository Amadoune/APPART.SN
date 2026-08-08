<?php

namespace Tests\Unit\ContentSeo\Outbox;

use Appart\Modules\ContentSeo\Application\Delivery\EditorialContentDeliveryPayload;
use Appart\Modules\ContentSeo\Application\Delivery\EditorialContentDeliveryStatus;
use Appart\Modules\ContentSeo\Application\Delivery\EditorialContentDeliveryV1;
use Appart\Modules\ContentSeo\Application\Delivery\OperationalSeoDeliveryPayload;
use Appart\Modules\ContentSeo\Application\Delivery\OperationalSeoDeliveryStatus;
use Appart\Modules\ContentSeo\Application\Delivery\OperationalSeoDeliveryV1;
use Appart\Modules\ContentSeo\Application\Event\EditorialContentEventType;
use Appart\Modules\ContentSeo\Application\Event\OperationalSeoEventType;
use Appart\Modules\ContentSeo\Application\Outbox\ContentSeoOutboxPolicy;
use Appart\Modules\ContentSeo\Application\Outbox\ContentSeoOutboxStatus;
use PHPUnit\Framework\TestCase;

final class ContentSeoOutboxPolicyTest extends TestCase
{
    public function test_identifiers_and_checksums_are_deterministic_and_stream_specific(): void
    {
        $policy = new ContentSeoOutboxPolicy;
        $editorial = new EditorialContentDeliveryV1(new EditorialContentDeliveryPayload(EditorialContentEventType::Observed, EditorialContentDeliveryStatus::Published, '2026-08-02T12:00:00.123456Z'));
        $operational = new OperationalSeoDeliveryV1(new OperationalSeoDeliveryPayload(OperationalSeoEventType::Observed, OperationalSeoDeliveryStatus::Indexable, '2026-08-02T12:00:00.123456Z'));

        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $policy->messageId($editorial));
        self::assertSame($policy->messageId($editorial), $policy->messageId($editorial));
        self::assertNotSame($policy->messageId($editorial), $policy->messageId($operational));
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $policy->checksum($editorial));
        self::assertNotSame($policy->messageId($editorial), $policy->checksum($editorial));
        self::assertSame(ContentSeoOutboxStatus::Applied, $policy->prepare($editorial)->status);
    }
}

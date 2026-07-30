<?php

namespace Tests\Feature;

use App\Application\ModerationEventOutbox\Contract\ModerationOutboxReaderV1;
use App\Application\ModerationOperationalAudit\Contract\ModerationOperationalAuditDeliveryReaderV1;
use App\Application\ModerationOperationalAudit\ModerationOperationalAuditConsumer;
use App\Application\ModerationOperationalAudit\ModerationOperationalAuditRecordFactory;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\Contract\AdministrationAuditAppendV1;
use Tests\TestCase;

final class ModerationOperationalAuditRuntimeTest extends TestCase
{
    public function test_operational_audit_composition_is_lazy_unique_and_singleton(): void
    {
        $this->app->instance(
            ModerationOutboxReaderV1::class,
            $this->createMock(ModerationOutboxReaderV1::class),
        );
        $this->app->instance(
            ModerationOperationalAuditDeliveryReaderV1::class,
            $this->createMock(ModerationOperationalAuditDeliveryReaderV1::class),
        );
        $this->app->instance(
            AdministrationAuditAppendV1::class,
            $this->createMock(AdministrationAuditAppendV1::class),
        );
        foreach ([ModerationOperationalAuditRecordFactory::class, ModerationOperationalAuditConsumer::class] as $component) {
            self::assertTrue($this->app->bound($component));
            self::assertFalse($this->app->resolved($component));
        }

        $consumer = $this->app->make(ModerationOperationalAuditConsumer::class);

        self::assertSame($consumer, $this->app->make(ModerationOperationalAuditConsumer::class));
        self::assertSame(
            $this->app->make(ModerationOperationalAuditRecordFactory::class),
            new \ReflectionProperty($consumer, 'records')->getValue($consumer),
        );
    }
}

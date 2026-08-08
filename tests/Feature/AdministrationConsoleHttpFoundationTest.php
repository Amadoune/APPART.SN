<?php

namespace Tests\Feature;

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
use Tests\TestCase;

final class AdministrationConsoleHttpFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(AdministrationOperatorReaderV1::class, new class implements AdministrationOperatorReaderV1
        {
            public function read(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationOperatorResultV1
            {
                return new AdministrationOperatorResultV1(AdministrationOperatorStatusV1::Available);
            }
        });
        $this->app->instance(AdministrationQueueReaderV1::class, new class implements AdministrationQueueReaderV1
        {
            public function read(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationQueueResultV1
            {
                return new AdministrationQueueResultV1(AdministrationQueueStatusV1::Empty);
            }
        });
        $this->app->instance(AdministrationAuditReaderV1::class, new class implements AdministrationAuditReaderV1
        {
            public function read(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationAuditResultV1
            {
                return new AdministrationAuditResultV1(AdministrationAuditStatusV1::Available);
            }
        });
    }

    public function test_endpoints_consume_public_contracts_and_return_only_status(): void
    {
        $query = '?subjectKey=administration%3A42&observedAt=2026-08-03T10%3A00%3A00.123456%2B00%3A00';

        $this->getJson('/api/administration/operator'.$query)->assertOk()->assertExactJson(['status' => 'available']);
        $this->getJson('/api/administration/queue'.$query)->assertOk()->assertExactJson(['status' => 'empty']);
        $this->getJson('/api/administration/audit'.$query)->assertOk()->assertExactJson(['status' => 'available']);
    }

    public function test_endpoints_reject_missing_and_unknown_inputs(): void
    {
        $this->getJson('/api/administration/operator')->assertUnprocessable();
        $this->getJson('/api/administration/audit?subjectKey=x&observedAt=2026-08-03T10%3A00%3A00.123456%2B00%3A00&secret=value')->assertUnprocessable();
    }
}

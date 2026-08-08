<?php

namespace Tests\Feature;

use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationCutoverReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationInventoryReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationQuarantineReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationReconciliationReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationWaveReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveStatusV1;
use Tests\TestCase;

final class LegacyMigrationHttpFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(LegacyMigrationInventoryReaderV1::class, new class implements LegacyMigrationInventoryReaderV1
        {
            public function read(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationInventoryResultV1
            {
                return new LegacyMigrationInventoryResultV1(LegacyMigrationInventoryStatusV1::Available, $observedAt);
            }
        });
        $this->app->instance(LegacyMigrationWaveReaderV1::class, new class implements LegacyMigrationWaveReaderV1
        {
            public function read(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationWaveResultV1
            {
                return new LegacyMigrationWaveResultV1(LegacyMigrationWaveStatusV1::Blocked, $observedAt);
            }
        });
        $this->app->instance(LegacyMigrationReconciliationReaderV1::class, new class implements LegacyMigrationReconciliationReaderV1
        {
            public function read(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationReconciliationResultV1
            {
                return new LegacyMigrationReconciliationResultV1(LegacyMigrationReconciliationStatusV1::Divergent, $observedAt);
            }
        });
        $this->app->instance(LegacyMigrationQuarantineReaderV1::class, new class implements LegacyMigrationQuarantineReaderV1
        {
            public function read(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationQuarantineResultV1
            {
                return new LegacyMigrationQuarantineResultV1(LegacyMigrationQuarantineStatusV1::ContainsItems, $observedAt);
            }
        });
        $this->app->instance(LegacyMigrationCutoverReaderV1::class, new class implements LegacyMigrationCutoverReaderV1
        {
            public function read(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationCutoverResultV1
            {
                return new LegacyMigrationCutoverResultV1(LegacyMigrationCutoverStatusV1::Ready, $observedAt);
            }
        });
    }

    public function test_five_public_endpoints_consume_only_public_readers(): void
    {
        $query = '?subjectKey=legacy-migration%3A42&observedAt=2026-08-04T10%3A00%3A00.123456%2B00%3A00';
        foreach (['inventory' => 'available', 'wave' => 'blocked', 'reconciliation' => 'divergent', 'quarantine' => 'contains_items', 'cutover' => 'ready'] as $path => $status) {
            $this->getJson('/api/legacy-migration/'.$path.$query)->assertOk()->assertExactJson(['status' => $status, 'observedAt' => '2026-08-04T10:00:00.123456Z']);
        }
    }

    public function test_requests_reject_missing_and_unknown_inputs(): void
    {
        $this->getJson('/api/legacy-migration/inventory')->assertUnprocessable();
        $this->getJson('/api/legacy-migration/cutover?subjectKey=x&observedAt=2026-08-04T10%3A00%3A00.123456%2B00%3A00&secret=value')->assertUnprocessable();
    }
}

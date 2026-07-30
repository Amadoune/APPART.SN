<?php

namespace Tests\Unit;

use App\Application\ModerationListingHandoff\ModerationListingHandoffStatus;
use App\Application\ModerationOrchestration\Contract\ModerationCommandStatus;
use App\Application\ModerationResidualOperationalAuditContract\ResidualListingCompletionV1;
use App\Application\ModerationResidualOperationalAuditContract\ResidualOperationalAuditConversionMatrixV1;
use App\Application\ModerationResidualOperationalAuditContract\ResidualOperationalAuditPathV1;
use App\Application\ModerationResidualOperationalAuditContract\ResidualOperationalAuditProductionPolicyV1;
use App\Application\ModerationResidualOperationalAuditContract\ResidualOperationalAuditRuntimeCatalogV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditOperationV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationResidualOperationalAuditContractTest extends TestCase
{
    #[Test]
    public function decision_and_close_produce_only_after_applied(): void
    {
        $policy = new ResidualOperationalAuditProductionPolicyV1;

        foreach ([
            ResidualOperationalAuditPathV1::DecisionApplied,
            ResidualOperationalAuditPathV1::CaseClosed,
        ] as $path) {
            foreach (ModerationCommandStatus::cases() as $status) {
                self::assertSame(
                    $status === ModerationCommandStatus::Applied,
                    $policy->producesAfterModerationCommand($path, $status),
                    $path->value.' / '.$status->value,
                );
            }
        }
    }

    #[Test]
    public function listing_applied_and_already_applied_have_one_canonical_completion(): void
    {
        $policy = new ResidualOperationalAuditProductionPolicyV1;

        self::assertSame(
            ResidualListingCompletionV1::Applied,
            $policy->normalizeListingResult(ModerationListingHandoffStatus::Applied),
        );
        self::assertSame(
            ResidualListingCompletionV1::Applied,
            $policy->normalizeListingResult(ModerationListingHandoffStatus::AlreadyApplied),
        );

        foreach (ModerationListingHandoffStatus::cases() as $status) {
            if (in_array($status, [
                ModerationListingHandoffStatus::Applied,
                ModerationListingHandoffStatus::AlreadyApplied,
            ], true)) {
                continue;
            }

            self::assertNull($policy->normalizeListingResult($status), $status->value);
        }
    }

    #[Test]
    public function runtime_audit_catalog_is_closed_to_exactly_seven_types(): void
    {
        $catalog = new ResidualOperationalAuditRuntimeCatalogV1;

        self::assertCount(7, $catalog->types());
        self::assertCount(7, array_unique($catalog->types()));
        foreach ($catalog->types() as $type) {
            self::assertTrue($catalog->accepts($type));
        }
        self::assertFalse($catalog->accepts('moderation.eighth-event.v1'));
    }

    #[Test]
    public function conversion_is_closed_and_uses_only_event_payload_keys(): void
    {
        $matrix = new ResidualOperationalAuditConversionMatrixV1;
        $expected = [
            ModerationEventTypeV1::ReportSubmitted->value => [AdministrationAuditOperationV1::ReportSubmitted, 'reportId'],
            ModerationEventTypeV1::ReportValidated->value => [AdministrationAuditOperationV1::ReportValidated, 'reportId'],
            'moderation.finding.recorded.v1' => [AdministrationAuditOperationV1::FindingRecorded, 'findingId'],
            'moderation.queue-item.claimed.v1' => [AdministrationAuditOperationV1::QueueItemClaimed, 'queueItemId'],
            ModerationEventTypeV1::DecisionIssued->value => [AdministrationAuditOperationV1::DecisionIssued, 'decisionId'],
            ModerationEventTypeV1::CaseClosed->value => [AdministrationAuditOperationV1::CaseClosed, 'currentDecisionId'],
            ModerationEventTypeV1::TargetActionCompleted->value => [AdministrationAuditOperationV1::ListingHandoffCompleted, 'decisionId'],
        ];

        foreach ($expected as $type => [$operation, $key]) {
            $conversion = $matrix->conversion($type);
            self::assertSame($operation, $conversion->operation);
            self::assertSame($key, $conversion->subjectKey);
        }

        $this->expectException(InvalidArgumentException::class);
        $matrix->conversion('moderation.unknown.v1');
    }
}

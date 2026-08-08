<?php

namespace Appart\Modules\SecurityCompliance\Infrastructure\Persistence;

use Appart\Modules\SecurityCompliance\Application\OwnerSource\ComplianceControlRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\IncidentRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\PrivacyPolicyRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecretInventoryRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityAuditRevisionState;
use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditStatusV1;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

/** @phpstan-type OwnerRow array{subject_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string,revision_checksum:string} */
final readonly class SecurityComplianceOwnerSourceMapper
{
    /** @return OwnerRow */
    public function secretInventoryToRow(SecretInventoryRevisionState $state): array
    {
        return $this->row($state->subjectKey, 'secret_inventory', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @return OwnerRow */
    public function securityAuditToRow(SecurityAuditRevisionState $state): array
    {
        return $this->row($state->subjectKey, 'security_audit', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @return OwnerRow */
    public function incidentToRow(IncidentRevisionState $state): array
    {
        return $this->row($state->subjectKey, 'incident', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @return OwnerRow */
    public function privacyPolicyToRow(PrivacyPolicyRevisionState $state): array
    {
        return $this->row($state->subjectKey, 'privacy_policy', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @return OwnerRow */
    public function complianceControlToRow(ComplianceControlRevisionState $state): array
    {
        return $this->row($state->subjectKey, 'compliance_control', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @param OwnerRow $row */
    public function toSecretInventoryState(array $row): SecretInventoryRevisionState
    {
        $this->assertValid($row, 'secret_inventory');

        return new SecretInventoryRevisionState($row['subject_key'], $row['revision'], SecretInventoryStatusV1::from($row['decision']), new DateTimeImmutable($row['effective_at']), new DateTimeImmutable($row['recorded_at']));
    }

    /** @param OwnerRow $row */
    public function toSecurityAuditState(array $row): SecurityAuditRevisionState
    {
        $this->assertValid($row, 'security_audit');

        return new SecurityAuditRevisionState($row['subject_key'], $row['revision'], SecurityAuditStatusV1::from($row['decision']), new DateTimeImmutable($row['effective_at']), new DateTimeImmutable($row['recorded_at']));
    }

    /** @param OwnerRow $row */
    public function toIncidentState(array $row): IncidentRevisionState
    {
        $this->assertValid($row, 'incident');

        return new IncidentRevisionState($row['subject_key'], $row['revision'], IncidentStatusV1::from($row['decision']), new DateTimeImmutable($row['effective_at']), new DateTimeImmutable($row['recorded_at']));
    }

    /** @param OwnerRow $row */
    public function toPrivacyPolicyState(array $row): PrivacyPolicyRevisionState
    {
        $this->assertValid($row, 'privacy_policy');

        return new PrivacyPolicyRevisionState($row['subject_key'], $row['revision'], PrivacyPolicyStatusV1::from($row['decision']), new DateTimeImmutable($row['effective_at']), new DateTimeImmutable($row['recorded_at']));
    }

    /** @param OwnerRow $row */
    public function toComplianceControlState(array $row): ComplianceControlRevisionState
    {
        $this->assertValid($row, 'compliance_control');

        return new ComplianceControlRevisionState($row['subject_key'], $row['revision'], ComplianceControlStatusV1::from($row['decision']), new DateTimeImmutable($row['effective_at']), new DateTimeImmutable($row['recorded_at']));
    }

    /** @return OwnerRow */
    private function row(string $key, string $stream, int $revision, string $decision, DateTimeImmutable $effective, DateTimeImmutable $recorded): array
    {
        $row = ['subject_key' => $key, 'stream_type' => $stream, 'revision' => $revision, 'decision' => $decision, 'effective_at' => $this->canonical($effective), 'recorded_at' => $this->canonical($recorded)];

        return $row + ['revision_checksum' => $this->checksum($row)];
    }

    /** @param OwnerRow $row */
    private function assertValid(array $row, string $stream): void
    {
        try {
            if ($row['stream_type'] !== $stream || ! hash_equals($row['revision_checksum'], $this->checksum($row))) {
                throw new RuntimeException('SecurityCompliance owner source checksum mismatch.');
            }
        } catch (Throwable $exception) {
            throw new RuntimeException('Invalid SecurityCompliance owner source row.', 0, $exception);
        }
    }

    /** @param array{subject_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string} $row */
    private function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [$row['subject_key'], $row['stream_type'], (string) $row['revision'], $row['decision'], $this->canonical(new DateTimeImmutable($row['effective_at'])), $this->canonical(new DateTimeImmutable($row['recorded_at']))]));
    }

    private function canonical(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}

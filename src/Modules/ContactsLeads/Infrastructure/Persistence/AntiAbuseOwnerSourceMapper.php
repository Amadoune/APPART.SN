<?php

namespace Appart\Modules\ContactsLeads\Infrastructure\Persistence;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionDecision;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionState;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

final readonly class AntiAbuseOwnerSourceMapper
{
    /** @return array{intent_id:string,revision:int,decision:string,effective_at:string,recorded_at:string,policy_reference:?string,checksum:string} */
    public function toRow(AntiAbuseRevisionState $revision): array
    {
        $row = [
            'intent_id' => $revision->intentId->value,
            'revision' => $revision->revision,
            'decision' => $revision->decision->value,
            'effective_at' => $this->canonical($revision->effectiveAt),
            'recorded_at' => $this->canonical($revision->recordedAt),
            'policy_reference' => $revision->policyReference,
        ];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @param array<string, mixed> $row */
    public function toState(array $row): AntiAbuseRevisionState
    {
        try {
            if (! hash_equals((string) $row['revision_checksum'], $this->checksum($row))) {
                throw new RuntimeException('Anti-abuse revision checksum mismatch.');
            }

            return new AntiAbuseRevisionState(
                LeadIngressIntentId::fromString((string) $row['lead_ingress_intent_id']),
                (int) $row['revision'],
                AntiAbuseRevisionDecision::from((string) $row['decision']),
                new DateTimeImmutable((string) $row['effective_at']),
                new DateTimeImmutable((string) $row['recorded_at']),
                $row['policy_reference'] === null ? null : (string) $row['policy_reference'],
            );
        } catch (Throwable $error) {
            throw new RuntimeException('Invalid anti-abuse owner source row.', 0, $error);
        }
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [
            strtolower((string) ($row['lead_ingress_intent_id'] ?? $row['intent_id'])),
            (string) $row['revision'],
            (string) $row['decision'],
            $this->canonical(new DateTimeImmutable((string) $row['effective_at'])),
            $this->canonical(new DateTimeImmutable((string) $row['recorded_at'])),
            $row['policy_reference'] === null ? '' : (string) $row['policy_reference'],
        ]));
    }

    private function canonical(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}

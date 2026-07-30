<?php

namespace Appart\Modules\Professionals\Infrastructure\Persistence;

use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceState;
use DateTimeImmutable;
use InvalidArgumentException;

final class ProfessionalMandateOwnerSourceMapper
{
    /** @return array<string, int|string> */
    public function parameters(ProfessionalMandateOwnerSourceState $state): array
    {
        $professionalIds = $state->professionalIds;
        $canonical = array_values(array_unique($professionalIds));
        sort($canonical, SORT_STRING);

        if ($canonical !== $professionalIds) {
            throw new InvalidArgumentException('Professional ids must be sorted and unique.');
        }
        foreach ([$state->accountId, $state->intentId, ...$professionalIds] as $id) {
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id) !== 1) {
                throw new InvalidArgumentException('Owner source ids must be canonical UUIDs.');
            }
        }
        if ($state->version < 1 || preg_match('/^[0-9a-f]{64}$/', $state->intentChecksum) !== 1) {
            throw new InvalidArgumentException('Owner source version or checksum is invalid.');
        }

        return [
            'account_id' => $state->accountId,
            'professional_ids' => json_encode($professionalIds, JSON_THROW_ON_ERROR),
            'version' => $state->version,
            'intent_id' => $state->intentId,
            'intent_checksum' => $state->intentChecksum,
            'recorded_at' => $state->recordedAt->format('Y-m-d\TH:i:s.uP'),
        ];
    }

    public function date(string $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value);
    }
}

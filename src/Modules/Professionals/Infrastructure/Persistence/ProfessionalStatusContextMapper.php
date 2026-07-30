<?php

namespace Appart\Modules\Professionals\Infrastructure\Persistence;

use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualAppend;

final readonly class ProfessionalStatusContextMapper
{
    /** @return array{professional_id:string,version:int,actor_id:string,occurred_at:string,context_checksum:string} */
    public function map(ProfessionalStatusContextualAppend $append): array
    {
        $row = [
            'professional_id' => $append->professionalId->value,
            'version' => $append->nextVersion(),
            'actor_id' => $append->context->actor->value,
            'occurred_at' => $append->context->occurredAt->value->format('Y-m-d\TH:i:s.uP'),
        ];

        return $row + ['context_checksum' => $this->checksum($row)];
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [(string) $row['professional_id'], (string) $row['version'], (string) $row['actor_id'], (string) $row['occurred_at']]));
    }
}

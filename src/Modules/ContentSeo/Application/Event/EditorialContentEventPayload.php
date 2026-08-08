<?php

namespace Appart\Modules\ContentSeo\Application\Event;

final readonly class EditorialContentEventPayload
{
    public function __construct(public EditorialContentEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}

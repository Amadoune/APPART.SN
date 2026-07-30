<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessEvent;

final class IdentityAccessEventCatalog
{
    /** @return list<IdentityAccessEventType> */
    public function events(): array
    {
        return IdentityAccessEventType::cases();
    }

    public function isPubliclyPublishable(IdentityAccessEventType $type): bool
    {
        return in_array($type, $this->events(), true);
    }
}

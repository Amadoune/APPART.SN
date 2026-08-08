<?php

namespace Appart\Modules\Notifications\Application\Runtime;

final readonly class NotificationsRuntimeDiagnostics
{
    public function __construct(
        public string $runtimeId,
        public string $version,
        public NotificationsRuntimeAvailability $availability,
    ) {}
}

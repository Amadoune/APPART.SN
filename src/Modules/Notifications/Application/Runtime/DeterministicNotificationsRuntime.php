<?php

namespace Appart\Modules\Notifications\Application\Runtime;

final readonly class DeterministicNotificationsRuntime implements NotificationsRuntimeV1
{
    private const RUNTIME_ID = 'notifications.owner-source';

    private const VERSION = 'notifications-runtime-v1';

    public function __construct(private NotificationsRuntimeAvailabilityPolicy $availabilityPolicy) {}

    public function availability(): NotificationsRuntimeAvailability
    {
        return $this->availabilityPolicy->inspect();
    }

    public function diagnostics(): NotificationsRuntimeDiagnostics
    {
        return new NotificationsRuntimeDiagnostics(self::RUNTIME_ID, self::VERSION, $this->availability());
    }
}

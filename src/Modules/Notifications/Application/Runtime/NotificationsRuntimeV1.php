<?php

namespace Appart\Modules\Notifications\Application\Runtime;

interface NotificationsRuntimeV1
{
    public function availability(): NotificationsRuntimeAvailability;

    public function diagnostics(): NotificationsRuntimeDiagnostics;
}

<?php

namespace Appart\Modules\AdministrationConsole\Application\Outbox;

interface AdministrationConsoleOutboxReader
{
    /** @return list<AdministrationConsoleOutboxResult> */
    public function pending(int $limit): array;
}

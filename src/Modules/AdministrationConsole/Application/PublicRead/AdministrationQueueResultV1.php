<?php

namespace Appart\Modules\AdministrationConsole\Application\PublicRead;

final readonly class AdministrationQueueResultV1
{
    public function __construct(public AdministrationQueueStatusV1 $status) {}
}

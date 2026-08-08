<?php

namespace Appart\Modules\AdministrationConsole\Application\PublicRead;

final readonly class AdministrationOperatorResultV1
{
    public function __construct(public AdministrationOperatorStatusV1 $status) {}
}

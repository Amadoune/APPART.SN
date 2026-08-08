<?php

namespace Appart\Modules\AdministrationConsole\Application\PublicRead;

final readonly class AdministrationAuditResultV1
{
    public function __construct(public AdministrationAuditStatusV1 $status) {}
}

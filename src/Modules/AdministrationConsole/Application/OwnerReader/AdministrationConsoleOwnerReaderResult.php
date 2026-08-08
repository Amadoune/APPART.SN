<?php

namespace Appart\Modules\AdministrationConsole\Application\OwnerReader;

final readonly class AdministrationConsoleOwnerReaderResult
{
    public function __construct(public AdministrationConsoleOwnerReaderStatus $status) {}
}

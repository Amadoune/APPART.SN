<?php

namespace App\Application\IdentityAccessHttp\Contract;

use App\Application\IdentityAccessHttp\IdentityAccessHttpCommand;
use App\Application\IdentityAccessHttp\IdentityAccessHttpResult;
use App\Application\IdentityAccessHttp\IdentityAccessSessionInspection;
use DateTimeImmutable;

interface IdentityAccessHttpRuntime
{
    public function execute(IdentityAccessHttpCommand $command): IdentityAccessHttpResult;

    public function inspectSession(string $secret, DateTimeImmutable $at): IdentityAccessSessionInspection;
}

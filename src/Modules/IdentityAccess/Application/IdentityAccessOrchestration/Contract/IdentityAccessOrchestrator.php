<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\Contract;

use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicCommand;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicWorkResult;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessOrchestrationResult;

interface IdentityAccessOrchestrator
{
    /** @param callable(): IdentityAccessAtomicWorkResult $work */
    public function authenticate(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult;

    /** @param callable(): IdentityAccessAtomicWorkResult $work */
    public function manageSession(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult;

    /** @param callable(): IdentityAccessAtomicWorkResult $work */
    public function recoverPassword(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult;

    /** @param callable(): IdentityAccessAtomicWorkResult $work */
    public function changeContact(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult;

    /** @param callable(): IdentityAccessAtomicWorkResult $work */
    public function swapClaim(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult;

    /** @param callable(): IdentityAccessAtomicWorkResult $work */
    public function mutateProfile(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult;

    /** @param callable(): IdentityAccessAtomicWorkResult $work */
    public function closeAccount(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult;

    /** @param callable(): IdentityAccessAtomicWorkResult $work */
    public function reopenAccount(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult;
}

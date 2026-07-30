<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration;

use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\Contract\IdentityAccessAtomicTransaction;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\Contract\IdentityAccessOrchestrator;
use LogicException;

final readonly class DeterministicIdentityAccessOrchestrator implements IdentityAccessOrchestrator
{
    public function __construct(private IdentityAccessAtomicTransaction $transaction) {}

    public function authenticate(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute(IdentityAccessAtomicOperation::Authentication, $command, $work);
    }

    public function manageSession(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute(IdentityAccessAtomicOperation::Session, $command, $work);
    }

    public function recoverPassword(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute(IdentityAccessAtomicOperation::PasswordRecovery, $command, $work);
    }

    public function changeContact(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute(IdentityAccessAtomicOperation::ContactChange, $command, $work);
    }

    public function swapClaim(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute(IdentityAccessAtomicOperation::ClaimSwap, $command, $work);
    }

    public function mutateProfile(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute(IdentityAccessAtomicOperation::ProfileMutation, $command, $work);
    }

    public function closeAccount(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute(IdentityAccessAtomicOperation::AccountClosure, $command, $work);
    }

    public function reopenAccount(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute(IdentityAccessAtomicOperation::Reopen, $command, $work);
    }

    /** @param callable(): IdentityAccessAtomicWorkResult $work */
    private function execute(
        IdentityAccessAtomicOperation $expected,
        IdentityAccessAtomicCommand $command,
        callable $work,
    ): IdentityAccessOrchestrationResult {
        if ($command->operation !== $expected) {
            throw new LogicException('Atomic operation does not match the orchestration entry point.');
        }

        return $this->transaction->execute($command, $work);
    }
}

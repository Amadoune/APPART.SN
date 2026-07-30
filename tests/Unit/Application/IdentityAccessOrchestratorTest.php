<?php

namespace Tests\Unit\Application;

use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\Contract\IdentityAccessAtomicTransaction;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\DeterministicIdentityAccessOrchestrator;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicCommand;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicOperation;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicWorkResult;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessOrchestrationResult;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessOrchestrationStatus;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdentityAccessOrchestratorTest extends TestCase
{
    #[Test]
    public function all_eight_entry_points_delegate_to_the_atomic_boundary(): void
    {
        $transaction = $this->createMock(IdentityAccessAtomicTransaction::class);
        $transaction->expects(self::exactly(8))->method('execute')->willReturnCallback(
            static fn (IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult => new IdentityAccessOrchestrationResult(
                $command->operation,
                $work() === IdentityAccessAtomicWorkResult::Applied
                    ? IdentityAccessOrchestrationStatus::Applied
                    : IdentityAccessOrchestrationStatus::Rejected,
            ),
        );
        $orchestrator = new DeterministicIdentityAccessOrchestrator($transaction);
        $work = static fn (): IdentityAccessAtomicWorkResult => IdentityAccessAtomicWorkResult::Applied;

        $results = [
            $orchestrator->authenticate($this->command(IdentityAccessAtomicOperation::Authentication), $work),
            $orchestrator->manageSession($this->command(IdentityAccessAtomicOperation::Session), $work),
            $orchestrator->recoverPassword($this->command(IdentityAccessAtomicOperation::PasswordRecovery), $work),
            $orchestrator->changeContact($this->command(IdentityAccessAtomicOperation::ContactChange), $work),
            $orchestrator->swapClaim($this->command(IdentityAccessAtomicOperation::ClaimSwap), $work),
            $orchestrator->mutateProfile($this->command(IdentityAccessAtomicOperation::ProfileMutation), $work),
            $orchestrator->closeAccount($this->command(IdentityAccessAtomicOperation::AccountClosure), $work),
            $orchestrator->reopenAccount($this->command(IdentityAccessAtomicOperation::Reopen), $work),
        ];

        self::assertSame(array_fill(0, 8, IdentityAccessOrchestrationStatus::Applied), array_column($results, 'status'));
    }

    #[Test]
    public function a_command_cannot_enter_the_wrong_operation(): void
    {
        $orchestrator = new DeterministicIdentityAccessOrchestrator($this->createStub(IdentityAccessAtomicTransaction::class));
        $this->expectException(LogicException::class);

        $orchestrator->authenticate(
            $this->command(IdentityAccessAtomicOperation::Session),
            static fn (): IdentityAccessAtomicWorkResult => IdentityAccessAtomicWorkResult::Applied,
        );
    }

    private function command(IdentityAccessAtomicOperation $operation): IdentityAccessAtomicCommand
    {
        return new IdentityAccessAtomicCommand(
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            str_repeat('a', 64),
            AccountId::fromString('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'),
            $operation,
            new DateTimeImmutable('2026-01-01T00:00:00Z'),
        );
    }
}

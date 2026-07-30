<?php

namespace App\Application\IdentityAccessEventIntegration;

use App\Application\IdentityAccessEventOutbox\Contract\IdentityAccessOutboxWriter;
use App\Application\IdentityAccessEventOutbox\IdentityAccessOutboxWriteResult;
use App\Application\IdentityAccessEventRouting\DeterministicIdentityAccessEventRouter;
use App\Application\IdentityAccessEventTransport\IdentityAccessDeliveryMessageV1;
use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventType;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\Contract\IdentityAccessOrchestrator;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicOperation;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicWorkResult;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessOrchestrationResult;
use RuntimeException;

final readonly class IdentityAccessAtomicEventOrchestrator
{
    public function __construct(
        private IdentityAccessOrchestrator $orchestrator,
        private DeterministicIdentityAccessEventRouter $router,
        private IdentityAccessOutboxWriter $outbox,
    ) {}

    public function execute(IdentityAccessAtomicEventRequest $request): IdentityAccessOrchestrationResult
    {
        $work = function () use ($request): IdentityAccessAtomicWorkResult {
            $result = ($request->work)();
            if ($result !== IdentityAccessAtomicWorkResult::Applied) {
                return $result;
            }

            foreach ($request->events as $event) {
                if (! $this->supports($request->command->operation, $event->type)) {
                    throw new RuntimeException('IAM event is incompatible with the atomic operation.');
                }
                $message = new IdentityAccessDeliveryMessageV1($event);
                $routing = $this->router->route($message);
                if (! $routing->routed) {
                    throw new RuntimeException('IAM event routing was rejected.');
                }
                $written = $this->outbox->append($message, $routing->destinations);
                if (! in_array($written, [
                    IdentityAccessOutboxWriteResult::Applied,
                    IdentityAccessOutboxWriteResult::AlreadyApplied,
                ], true)) {
                    throw new RuntimeException('IAM Outbox rejected the atomic event.');
                }
            }

            return $result;
        };

        return match ($request->command->operation) {
            IdentityAccessAtomicOperation::Authentication => $this->orchestrator->authenticate($request->command, $work),
            IdentityAccessAtomicOperation::Session => $this->orchestrator->manageSession($request->command, $work),
            IdentityAccessAtomicOperation::PasswordRecovery => $this->orchestrator->recoverPassword($request->command, $work),
            IdentityAccessAtomicOperation::ContactChange => $this->orchestrator->changeContact($request->command, $work),
            IdentityAccessAtomicOperation::ClaimSwap => $this->orchestrator->swapClaim($request->command, $work),
            IdentityAccessAtomicOperation::ProfileMutation => $this->orchestrator->mutateProfile($request->command, $work),
            IdentityAccessAtomicOperation::AccountClosure => $this->orchestrator->closeAccount($request->command, $work),
            IdentityAccessAtomicOperation::Reopen => $this->orchestrator->reopenAccount($request->command, $work),
        };
    }

    private function supports(IdentityAccessAtomicOperation $operation, IdentityAccessEventType $event): bool
    {
        return match ($operation) {
            IdentityAccessAtomicOperation::ContactChange => in_array($event, [
                IdentityAccessEventType::ProfileEmailChanged,
                IdentityAccessEventType::ProfilePhoneChanged,
            ], true),
            IdentityAccessAtomicOperation::ProfileMutation => $event === IdentityAccessEventType::ProfileNameChanged,
            IdentityAccessAtomicOperation::AccountClosure => in_array($event, [
                IdentityAccessEventType::ClosureRequested,
                IdentityAccessEventType::AccountClosed,
            ], true),
            IdentityAccessAtomicOperation::Reopen => $event === IdentityAccessEventType::AccountReopened,
            IdentityAccessAtomicOperation::Authentication,
            IdentityAccessAtomicOperation::Session,
            IdentityAccessAtomicOperation::PasswordRecovery,
            IdentityAccessAtomicOperation::ClaimSwap => false,
        };
    }
}

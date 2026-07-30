<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProfessionalStatusReplayPolicyAmendmentArchitectureTest extends TestCase
{
    public function test_policy_accepts_action_and_never_requests_or_reconstructs_transition(): void
    {
        $policy = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Professionals/Application/ProfessionalStatusTransitionContext/ProfessionalStatusReplayPolicy.php');

        self::assertStringContainsString('ProfessionalStatusAction $requestedAction', $policy);
        self::assertStringContainsString('$inspected->transition->action !== $requestedAction', $policy);
        self::assertStringNotContainsString('ProfessionalStatusTransition $requestedTransition', $policy);

        foreach (['ProfessionalStatusWorkflow', '->decide(', 'new ProfessionalStatusTransition', 'ProfessionalStatusState', 'match (', 'default'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $policy);
        }
    }

    public function test_amendment_introduces_no_runtime_postgresql_or_delivery_component(): void
    {
        $root = dirname(__DIR__, 2);
        $policy = (string) file_get_contents($root.'/src/Modules/Professionals/Application/ProfessionalStatusTransitionContext/ProfessionalStatusReplayPolicy.php');

        foreach (['PDO', 'PostgreSql', 'Repository', 'Migration', 'Http', 'Event', 'Inbox', 'Outbox', 'Consumer', 'Worker', 'Eligibility'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $policy);
        }

    }
}

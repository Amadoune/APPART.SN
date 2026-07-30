<?php

namespace Tests\Support;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualAppend;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualInspectionStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualWriteResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualReplayInspector;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualTransitionStore;
use PHPUnit\Framework\TestCase;

abstract class AdministrativeActionContextualTransitionStoreContract extends TestCase
{
    abstract protected function store(): AdministrativeActionContextualTransitionStore;

    abstract protected function inspector(): AdministrativeActionContextualReplayInspector;

    abstract protected function validAppend(): AdministrativeActionContextualAppend;

    abstract protected function divergentAppend(): AdministrativeActionContextualAppend;

    abstract protected function seedEnrolled(): void;

    abstract protected function resetPersistence(): void;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetPersistence();
        $this->seedEnrolled();
    }

    public function test_contract_applies_and_inspects_the_exact_append(): void
    {
        $append = $this->validAppend();
        self::assertSame(AdministrativeActionContextualWriteResult::Applied, $this->store()->append($append));
        $inspection = $this->inspector()->inspectLatest($append->actionId);
        self::assertSame(AdministrativeActionContextualInspectionStatus::Found, $inspection->status);
        self::assertNotNull($inspection->snapshot);
        self::assertEquals($append->transition, $inspection->snapshot->transition);
        self::assertSame($append->context->checksum()->value, $inspection->snapshot->checksum->value);
    }

    public function test_contract_is_strictly_idempotent(): void
    {
        $append = $this->validAppend();
        self::assertSame(AdministrativeActionContextualWriteResult::Applied, $this->store()->append($append));
        self::assertSame(AdministrativeActionContextualWriteResult::AlreadyApplied, $this->store()->append($append));
    }

    public function test_contract_reports_context_divergence(): void
    {
        self::assertSame(AdministrativeActionContextualWriteResult::Applied, $this->store()->append($this->validAppend()));
        self::assertSame(AdministrativeActionContextualWriteResult::ContextDivergence, $this->store()->append($this->divergentAppend()));
    }
}

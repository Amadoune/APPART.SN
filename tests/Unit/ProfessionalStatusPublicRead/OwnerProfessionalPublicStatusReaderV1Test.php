<?php

namespace Tests\Unit\ProfessionalStatusPublicRead;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\Contract\ProfessionalStatusWorkflowStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusPersistenceReadResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusStoredState;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\ProfessionalPublicStatusDecisionV1;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use Appart\Modules\Professionals\Infrastructure\Runtime\OwnerProfessionalPublicStatusReaderV1;
use PDOException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OwnerProfessionalPublicStatusReaderV1Test extends TestCase
{
    #[Test]
    public function it_translates_only_the_five_certified_public_decisions(): void
    {
        $id = ProfessionalId::fromString('63000000-0000-4000-8000-000000000201');
        $statusId = ProfessionalStatusId::fromString($id->value);
        $cases = [
            [ProfessionalStatusPersistenceReadResult::found(new ProfessionalStatusStoredState($statusId, ProfessionalStatusState::Active, 1)), ProfessionalPublicStatusDecisionV1::Available],
            [ProfessionalStatusPersistenceReadResult::found(new ProfessionalStatusStoredState($statusId, ProfessionalStatusState::Suspended, 1)), ProfessionalPublicStatusDecisionV1::Unavailable],
            [ProfessionalStatusPersistenceReadResult::missing($statusId), ProfessionalPublicStatusDecisionV1::Missing],
            [ProfessionalStatusPersistenceReadResult::corrupted($statusId), ProfessionalPublicStatusDecisionV1::Corrupted],
        ];

        foreach ($cases as [$stored, $expected]) {
            $store = $this->createMock(ProfessionalStatusWorkflowStore::class);
            $store->expects(self::once())->method('read')->willReturn($stored);

            self::assertSame($expected, (new OwnerProfessionalPublicStatusReaderV1($store))->read($id));
        }

        $unavailableStore = $this->createMock(ProfessionalStatusWorkflowStore::class);
        $unavailableStore->method('read')->willThrowException(new PDOException('unavailable'));
        self::assertSame(
            ProfessionalPublicStatusDecisionV1::DependencyUnavailable,
            (new OwnerProfessionalPublicStatusReaderV1($unavailableStore))->read($id),
        );
    }
}

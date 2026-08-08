<?php

namespace Tests\Unit\Professionals\ProfessionalLeadRecipientPublicRead;

use Appart\Modules\Professionals\Application\ProfessionalLeadRecipientPublicRead\Contract\ProfessionalLeadRecipientReaderV1;
use Appart\Modules\Professionals\Application\ProfessionalLeadRecipientPublicRead\LeadRecipientObservedAt;
use Appart\Modules\Professionals\Application\ProfessionalLeadRecipientPublicRead\ProfessionalLeadRecipientResultV1;
use Appart\Modules\Professionals\Application\ProfessionalLeadRecipientPublicRead\ProfessionalLeadRecipientStatusV1;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class ProfessionalLeadRecipientPublicReadContractTest extends TestCase
{
    #[Test]
    public function result_catalog_is_closed_and_fail_closed(): void
    {
        self::assertSame([
            'eligible',
            'not_eligible',
            'missing',
            'corrupted',
            'dependency_unavailable',
        ], array_column(ProfessionalLeadRecipientStatusV1::cases(), 'value'));

        self::assertSame(
            [ProfessionalLeadRecipientStatusV1::Eligible],
            array_values(array_filter(
                ProfessionalLeadRecipientStatusV1::cases(),
                static fn (ProfessionalLeadRecipientStatusV1 $status): bool => $status === ProfessionalLeadRecipientStatusV1::Eligible,
            )),
        );
        self::assertSame(
            [
                ProfessionalLeadRecipientStatusV1::Eligible,
                ProfessionalLeadRecipientStatusV1::NotEligible,
                ProfessionalLeadRecipientStatusV1::Missing,
                ProfessionalLeadRecipientStatusV1::Corrupted,
                ProfessionalLeadRecipientStatusV1::DependencyUnavailable,
            ],
            [
                ProfessionalLeadRecipientResultV1::eligible()->status,
                ProfessionalLeadRecipientResultV1::notEligible()->status,
                ProfessionalLeadRecipientResultV1::missing()->status,
                ProfessionalLeadRecipientResultV1::corrupted()->status,
                ProfessionalLeadRecipientResultV1::dependencyUnavailable()->status,
            ],
        );
    }

    #[Test]
    public function reader_signature_is_minimal_read_only_and_versioned(): void
    {
        $method = new ReflectionMethod(ProfessionalLeadRecipientReaderV1::class, 'read');
        $parameters = $method->getParameters();

        self::assertSame(2, $method->getNumberOfRequiredParameters());
        self::assertSame(ProfessionalId::class, (string) $parameters[0]->getType());
        self::assertSame('professionalId', $parameters[0]->getName());
        self::assertSame(LeadRecipientObservedAt::class, (string) $parameters[1]->getType());
        self::assertSame('observedAt', $parameters[1]->getName());
        self::assertSame(ProfessionalLeadRecipientResultV1::class, (string) $method->getReturnType());
        self::assertTrue((new ReflectionClass(ProfessionalLeadRecipientResultV1::class))->isReadOnly());
    }

    #[Test]
    public function observed_at_is_immutable_utc_canonical_and_does_not_mutate_its_source(): void
    {
        $source = new DateTimeImmutable('2026-07-31T10:15:30.123456+02:00');
        $observedAt = new LeadRecipientObservedAt($source);

        self::assertTrue((new ReflectionClass($observedAt))->isReadOnly());
        self::assertSame('2026-07-31T08:15:30.123456Z', $observedAt->canonical());
        self::assertSame('UTC', $observedAt->value->getTimezone()->getName());
        self::assertSame('+02:00', $source->format('P'));
    }
}

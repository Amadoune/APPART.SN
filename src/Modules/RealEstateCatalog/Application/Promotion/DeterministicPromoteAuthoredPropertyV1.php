<?php

namespace Appart\Modules\RealEstateCatalog\Application\Promotion;

use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\AddressIntentId;
use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\Contract\AddressIdentityIssuerV1;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringSourceCompleteness;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use Appart\Modules\RealEstateCatalog\Application\BusinessYear\Contract\BusinessYearAuthorityV1;
use Appart\Modules\RealEstateCatalog\Application\BusinessYear\PropertyDecisionOccurredAt;
use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromoteAuthoredPropertyV1;
use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromotionCommandLedger;
use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromotionTransaction;
use Appart\Modules\RealEstateCatalog\Application\UseCase\RegisterProperty;
use Appart\Modules\RealEstateCatalog\Domain\Exception\RealEstateCatalogException;
use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use InvalidArgumentException;
use Throwable;
use ValueError;

final readonly class DeterministicPromoteAuthoredPropertyV1 implements PromoteAuthoredPropertyV1
{
    public function __construct(
        private PropertyAuthoringStore $authoring,
        private PropertyRegistry $properties,
        private RegisterProperty $register,
        private AddressIdentityIssuerV1 $addresses,
        private BusinessYearAuthorityV1 $businessYears,
        private PromotionCommandLedger $ledger,
        private PromotionTransaction $transaction,
    ) {}

    public function promote(PromoteAuthoredPropertyCommand $command): PromoteAuthoredPropertyResult
    {
        try {
            /** @var PromoteAuthoredPropertyResult $result */
            $result = $this->transaction->run($command->commandId, $command->propertyId, fn (): PromoteAuthoredPropertyResult => $this->apply($command));

            return $result;
        } catch (Throwable) {
            return $this->result(PromoteAuthoredPropertyStatus::DependencyUnavailable);
        }
    }

    private function apply(PromoteAuthoredPropertyCommand $command): PromoteAuthoredPropertyResult
    {
        $snapshot = $this->authoring->read($command->propertyId);
        if ($snapshot === null) {
            return $this->result(PromoteAuthoredPropertyStatus::AuthoringMissing);
        }
        $record = $this->ledger->find($command->commandId);
        if ($record !== null) {
            return $this->result($record->matches($command, $snapshot)
                ? PromoteAuthoredPropertyStatus::AlreadyApplied
                : PromoteAuthoredPropertyStatus::DivergentCommand);
        }
        if (! hash_equals(strtolower($snapshot->ownerAccountId), strtolower($command->ownerAccountId))) {
            return $this->result(PromoteAuthoredPropertyStatus::OwnershipMismatch);
        }
        if ($snapshot->version !== $command->expectedAuthoringVersion) {
            return $this->result(PromoteAuthoredPropertyStatus::VersionConflict);
        }
        if ($snapshot->completeness() !== PropertyAuthoringSourceCompleteness::CompleteForPromotion) {
            return $this->result(PromoteAuthoredPropertyStatus::IncompleteAuthoring);
        }

        try {
            $arguments = $this->arguments($snapshot, $command);
            $existing = $this->properties->find($arguments->propertyId);
            if ($existing !== null) {
                return $this->result($this->compatible($existing, $arguments)
                    ? PromoteAuthoredPropertyStatus::AlreadyApplied
                    : PromoteAuthoredPropertyStatus::DivergentCommand);
            }
            $this->register->execute(
                $arguments->propertyId,
                $arguments->reference,
                $arguments->type,
                $arguments->surface,
                $arguments->rooms,
                $arguments->bathrooms,
                $arguments->constructionYear,
                $arguments->address,
                $arguments->businessYear,
                $command->occurredAt,
            );
        } catch (InvalidArgumentException|RealEstateCatalogException|ValueError) {
            return $this->result(PromoteAuthoredPropertyStatus::DomainRejected);
        }

        $this->ledger->record(new PromotionCommandRecord(
            strtolower($command->commandId),
            PromotionCommandChecksum::for($command, $snapshot),
            strtolower($command->propertyId),
            strtolower($command->ownerAccountId),
            $command->expectedAuthoringVersion,
            PromotionCommandChecksum::instant($command->occurredAt),
        ));

        return $this->result(PromoteAuthoredPropertyStatus::Applied);
    }

    private function arguments(PropertyAuthoringState $snapshot, PromoteAuthoredPropertyCommand $command): PromotionArguments
    {
        $propertyId = PropertyId::fromString($snapshot->propertyId);
        $addressId = $this->addresses->issue($propertyId, AddressIntentId::fromString((string) $snapshot->addressIntentId))->addressId;
        $businessYear = $this->businessYears->resolve(PropertyDecisionOccurredAt::fromString(PromotionCommandChecksum::instant($command->occurredAt)))->businessYear;

        return new PromotionArguments(
            $propertyId,
            PropertyReference::fromString((string) $snapshot->propertyReference),
            PropertyType::from((string) $snapshot->propertyType),
            $snapshot->surfaceSquareMeters === null ? null : SurfaceArea::fromSquareMeters($snapshot->surfaceSquareMeters),
            RoomCount::fromInt((int) $snapshot->rooms),
            BathroomCount::fromInt((int) $snapshot->bathrooms),
            $snapshot->constructionYear === null ? null : ConstructionYear::fromInt($snapshot->constructionYear),
            new Address($addressId, GeographicPlaceId::fromString((string) $snapshot->geographicPlaceId), AddressLine::fromString((string) $snapshot->addressLine)),
            $businessYear,
        );
    }

    private function compatible(Property $property, PromotionArguments $arguments): bool
    {
        return $property->version() === 0
            && $property->reference()->equals($arguments->reference)
            && $property->type() === $arguments->type
            && $property->surface()?->squareMeters === $arguments->surface?->squareMeters
            && $property->rooms()->equals($arguments->rooms)
            && $property->bathrooms()->equals($arguments->bathrooms)
            && $property->constructionYear()?->value === $arguments->constructionYear?->value
            && $this->addressesCompatible($property->address(), $arguments->address);
    }

    private function addressesCompatible(?Address $existing, ?Address $expected): bool
    {
        if ($existing === null || $expected === null) {
            return $existing === null && $expected === null;
        }

        return $existing->id->equals($expected->id)
            && $existing->equals($expected);
    }

    private function result(PromoteAuthoredPropertyStatus $status): PromoteAuthoredPropertyResult
    {
        return new PromoteAuthoredPropertyResult($status);
    }
}

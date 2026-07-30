<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Model;

use Appart\Modules\RealEstateCatalog\Domain\Event\AbstractPropertyEvent;
use Appart\Modules\RealEstateCatalog\Domain\Event\AddressChanged;
use Appart\Modules\RealEstateCatalog\Domain\Event\PropertyArchived;
use Appart\Modules\RealEstateCatalog\Domain\Event\PropertyEvent;
use Appart\Modules\RealEstateCatalog\Domain\Event\PropertyRegistered;
use Appart\Modules\RealEstateCatalog\Domain\Event\PropertyUpdated;
use Appart\Modules\RealEstateCatalog\Domain\Event\SurfaceChanged;
use Appart\Modules\RealEstateCatalog\Domain\Exception\InvalidPropertyValue;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyViolation;
use Appart\Modules\RealEstateCatalog\Domain\Policy\PropertyTypePolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyStatus;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use DateTimeImmutable;

final class Property
{
    /** @var list<PropertyEvent> */
    private array $events = [];

    private PropertyStatus $status = PropertyStatus::Active;

    private int $version = 0;

    private function __construct(private readonly PropertyId $id, private readonly PropertyReference $reference, private PropertyType $type, private ?SurfaceArea $surface, private RoomCount $rooms, private BathroomCount $bathrooms, private ?ConstructionYear $constructionYear, private ?Address $address, private DateTimeImmutable $lastChangedAt) {}

    public static function register(PropertyId $id, PropertyReference $reference, PropertyType $type, ?SurfaceArea $surface, RoomCount $rooms, BathroomCount $bathrooms, ?ConstructionYear $constructionYear, ?Address $address, BusinessYear $businessYear, PropertyTypePolicy $policy, DateTimeImmutable $at): self
    {
        $policy->assertValid($type, $surface, $rooms, $bathrooms, $constructionYear, $address, $businessYear);
        $property = new self($id, $reference, $type, $surface, $rooms, $bathrooms, $constructionYear, $address, $at);
        $property->recordEvent(new PropertyRegistered($id, $reference, $type, $surface, $rooms, $bathrooms, $constructionYear, $address, $at), 0);

        return $property;
    }

    public static function reconstitute(PropertyId $id, PropertyReference $reference, PropertyType $type, ?SurfaceArea $surface, RoomCount $rooms, BathroomCount $bathrooms, ?ConstructionYear $constructionYear, ?Address $address, PropertyStatus $status, DateTimeImmutable $lastChangedAt, int $version): self
    {
        if ($version < 0) {
            throw InvalidPropertyValue::field('version');
        }
        $property = new self($id, $reference, $type, $surface, $rooms, $bathrooms, $constructionYear, $address, $lastChangedAt);
        $property->status = $status;
        $property->version = $version;

        return $property;
    }

    public function update(PropertyType $type, ?SurfaceArea $surface, RoomCount $rooms, BathroomCount $bathrooms, ?ConstructionYear $constructionYear, BusinessYear $businessYear, PropertyTypePolicy $policy, DateTimeImmutable $at): void
    {
        $this->guardMutable();
        $this->guardTime($at);
        $policy->assertValid($type, $surface, $rooms, $bathrooms, $constructionYear, $this->address, $businessYear);
        $surfaceChanged = $surface?->squareMeters !== $this->surface?->squareMeters;
        $changed = $type !== $this->type || $surfaceChanged || ! $rooms->equals($this->rooms) || ! $bathrooms->equals($this->bathrooms) || $constructionYear?->value !== $this->constructionYear?->value;
        if (! $changed) {
            throw PropertyViolation::unchangedDetails();
        }
        $previousSurface = $this->surface;
        $this->type = $type;
        $this->surface = $surface;
        $this->rooms = $rooms;
        $this->bathrooms = $bathrooms;
        $this->constructionYear = $constructionYear;
        $this->recordEvent(new PropertyUpdated($this->id, $type, $surface, $rooms, $bathrooms, $constructionYear, $at));
        if ($surfaceChanged) {
            $this->recordEvent(new SurfaceChanged($this->id, $previousSurface, $surface, $at));
        }
        $this->changed($at);
    }

    public function changeAddress(Address $address, DateTimeImmutable $at): void
    {
        $this->guardMutable();
        $this->guardTime($at);
        if ($this->address?->equals($address) === true) {
            throw PropertyViolation::unchangedAddress();
        }
        $previous = $this->address;
        $this->address = $address;
        $this->recordEvent(new AddressChanged($this->id, $previous, $address, $at));
        $this->changed($at);
    }

    public function archive(DateTimeImmutable $at): void
    {
        if ($this->status === PropertyStatus::Archived) {
            throw PropertyViolation::alreadyArchived();
        }
        $this->guardTime($at);
        $this->status = PropertyStatus::Archived;
        $this->recordEvent(new PropertyArchived($this->id, $at));
        $this->changed($at);
    }

    public function id(): PropertyId
    {
        return $this->id;
    }

    public function reference(): PropertyReference
    {
        return $this->reference;
    }

    public function type(): PropertyType
    {
        return $this->type;
    }

    public function surface(): ?SurfaceArea
    {
        return $this->surface;
    }

    public function rooms(): RoomCount
    {
        return $this->rooms;
    }

    public function bathrooms(): BathroomCount
    {
        return $this->bathrooms;
    }

    public function constructionYear(): ?ConstructionYear
    {
        return $this->constructionYear;
    }

    public function address(): ?Address
    {
        return $this->address;
    }

    public function status(): PropertyStatus
    {
        return $this->status;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function lastChangedAt(): DateTimeImmutable
    {
        return $this->lastChangedAt;
    }

    /** @return list<PropertyEvent> */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    private function guardMutable(): void
    {
        if ($this->status === PropertyStatus::Archived) {
            throw PropertyViolation::archived();
        }
    }

    private function guardTime(DateTimeImmutable $at): void
    {
        if ($at < $this->lastChangedAt) {
            throw InvalidPropertyValue::field('event_time');
        }
    }

    private function changed(DateTimeImmutable $at): void
    {
        $this->lastChangedAt = $at;
        $this->version++;
    }

    private function recordEvent(PropertyEvent $event, ?int $resultVersion = null): void
    {
        if ($event instanceof AbstractPropertyEvent) {
            $event->stamp($resultVersion ?? $this->version + 1, count($this->events) + 1);
        }
        $this->events[] = $event;
    }
}

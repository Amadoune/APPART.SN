<?php

namespace Appart\Modules\Geography\Domain\Model;

use Appart\Modules\Geography\Domain\Event\PlaceCreated;
use Appart\Modules\Geography\Domain\Event\PlaceDisabled;
use Appart\Modules\Geography\Domain\Event\PlaceEnabled;
use Appart\Modules\Geography\Domain\Event\PlaceEvent;
use Appart\Modules\Geography\Domain\Event\PlaceMerged;
use Appart\Modules\Geography\Domain\Event\PlaceRenamed;
use Appart\Modules\Geography\Domain\Exception\CannotMergePlaceIntoItself;
use Appart\Modules\Geography\Domain\Exception\InvalidMergeTarget;
use Appart\Modules\Geography\Domain\Exception\InvalidPlaceHierarchy;
use Appart\Modules\Geography\Domain\Exception\InvalidPlaceState;
use Appart\Modules\Geography\Domain\Exception\PlaceAlreadyDisabled;
use Appart\Modules\Geography\Domain\Exception\PlaceAlreadyEnabled;
use Appart\Modules\Geography\Domain\Exception\PlaceAlreadyMerged;
use Appart\Modules\Geography\Domain\Exception\PlaceNameUnchanged;
use Appart\Modules\Geography\Domain\ValueObject\Coordinates;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use DateTimeImmutable;

final class Place
{
    /** @var array<string, GeographicAlias> */
    private array $aliases = [];

    /** @var list<PlaceEvent> */
    private array $recordedEvents = [];

    private bool $enabled = true;

    private ?PlaceId $mergedInto = null;

    private function __construct(
        private readonly PlaceId $id,
        private PlaceName $officialName,
        private readonly PlaceCode $code,
        private readonly PlaceType $type,
        private readonly CountryCode $countryCode,
        private readonly ?AdministrativeDivision $parent,
        private readonly ?Coordinates $coordinates,
        private int $version,
    ) {}

    public static function create(
        PlaceId $id,
        PlaceName $officialName,
        PlaceCode $code,
        PlaceType $type,
        CountryCode $countryCode,
        DateTimeImmutable $occurredAt,
        ?self $parent = null,
        ?Coordinates $coordinates = null,
    ): self {
        self::guardHierarchy($id, $type, $countryCode, $parent);

        $parentReference = $parent === null ? null : new AdministrativeDivision($parent->id, $parent->type);
        $place = new self($id, $officialName, $code, $type, $countryCode, $parentReference, $coordinates, 1);
        $place->record(new PlaceCreated(
            $id,
            $officialName,
            $code,
            $type,
            $countryCode,
            $parentReference?->placeId,
            $coordinates,
            $occurredAt,
            1,
        ));

        return $place;
    }

    /** @param list<GeographicAlias> $aliases */
    public static function reconstitute(
        PlaceId $id,
        PlaceName $officialName,
        PlaceCode $code,
        PlaceType $type,
        CountryCode $countryCode,
        ?AdministrativeDivision $parent,
        ?Coordinates $coordinates,
        array $aliases,
        bool $enabled,
        ?PlaceId $mergedInto,
        int $version,
    ): self {
        if ($version < 1 || ($mergedInto !== null && $enabled)) {
            throw new InvalidPlaceState;
        }

        self::guardReconstitutedHierarchy($id, $type, $parent);
        $place = new self($id, $officialName, $code, $type, $countryCode, $parent, $coordinates, $version);
        $place->enabled = $enabled;
        $place->mergedInto = $mergedInto;

        foreach ($aliases as $alias) {
            if ($alias->name->equals($officialName)) {
                throw new InvalidPlaceState;
            }
            $place->aliases[$alias->name->normalizationKey()] = $alias;
        }

        return $place;
    }

    public function rename(PlaceName $newName, DateTimeImmutable $occurredAt): void
    {
        $this->guardNotMerged();

        if ($this->officialName->equals($newName)) {
            throw new PlaceNameUnchanged;
        }

        $previousName = $this->officialName;
        $when = $occurredAt;

        unset($this->aliases[$newName->normalizationKey()]);
        $this->aliases[$previousName->normalizationKey()] ??= new GeographicAlias($previousName, $when);
        $this->officialName = $newName;
        $this->version++;
        $this->record(new PlaceRenamed($this->id, $previousName, $newName, $when, $this->version));
    }

    public function mergeInto(self $target, DateTimeImmutable $occurredAt): void
    {
        $this->guardNotMerged();

        if ($this->id->equals($target->id)) {
            throw new CannotMergePlaceIntoItself;
        }

        if (! $target->enabled || $target->mergedInto !== null) {
            throw InvalidMergeTarget::inactive();
        }

        if ($this->type !== $target->type) {
            throw InvalidMergeTarget::differentType();
        }

        if ($this->countryCode->value !== $target->countryCode->value) {
            throw InvalidMergeTarget::differentCountry();
        }

        $this->mergedInto = $target->id;
        $this->enabled = false;
        $this->version++;
        $this->record(new PlaceMerged($this->id, $target->id, $occurredAt, $this->version));
    }

    public function disable(DateTimeImmutable $occurredAt): void
    {
        $this->guardNotMerged();

        if (! $this->enabled) {
            throw new PlaceAlreadyDisabled;
        }

        $this->enabled = false;
        $this->version++;
        $this->record(new PlaceDisabled($this->id, $occurredAt, $this->version));
    }

    public function enable(DateTimeImmutable $occurredAt): void
    {
        $this->guardNotMerged();

        if ($this->enabled) {
            throw new PlaceAlreadyEnabled;
        }

        $this->enabled = true;
        $this->version++;
        $this->record(new PlaceEnabled($this->id, $occurredAt, $this->version));
    }

    public function id(): PlaceId
    {
        return $this->id;
    }

    public function officialName(): PlaceName
    {
        return $this->officialName;
    }

    public function code(): PlaceCode
    {
        return $this->code;
    }

    public function type(): PlaceType
    {
        return $this->type;
    }

    public function countryCode(): CountryCode
    {
        return $this->countryCode;
    }

    public function parent(): ?AdministrativeDivision
    {
        return $this->parent;
    }

    public function coordinates(): ?Coordinates
    {
        return $this->coordinates;
    }

    /** @return list<GeographicAlias> */
    public function aliases(): array
    {
        return array_values($this->aliases);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function mergedInto(): ?PlaceId
    {
        return $this->mergedInto;
    }

    public function version(): int
    {
        return $this->version;
    }

    /** @return list<PlaceEvent> */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    private static function guardHierarchy(PlaceId $id, PlaceType $type, CountryCode $countryCode, ?self $parent): void
    {
        if ($type === PlaceType::Country && $parent !== null) {
            throw InvalidPlaceHierarchy::countryCannotHaveParent();
        }

        if ($type !== PlaceType::Country && $parent === null) {
            throw InvalidPlaceHierarchy::parentIsRequired($type);
        }

        if ($parent === null) {
            return;
        }

        if ($id->equals($parent->id)) {
            throw InvalidPlaceHierarchy::selfParenting();
        }

        if ($parent->mergedInto !== null) {
            throw InvalidPlaceHierarchy::parentMerged();
        }

        if (! $parent->enabled) {
            throw InvalidPlaceHierarchy::parentDisabled();
        }

        if ($countryCode->value !== $parent->countryCode->value) {
            throw InvalidPlaceHierarchy::differentCountry();
        }

        if (! $type->acceptsParent($parent->type)) {
            throw InvalidPlaceHierarchy::incompatible($type, $parent->type);
        }
    }

    private static function guardReconstitutedHierarchy(PlaceId $id, PlaceType $type, ?AdministrativeDivision $parent): void
    {
        if (($type === PlaceType::Country) !== ($parent === null)) {
            throw new InvalidPlaceState;
        }

        if ($parent !== null && ($id->equals($parent->placeId) || ! $type->acceptsParent($parent->placeType))) {
            throw new InvalidPlaceState;
        }
    }

    private function guardNotMerged(): void
    {
        if ($this->mergedInto !== null) {
            throw new PlaceAlreadyMerged;
        }
    }

    private function record(PlaceEvent $event): void
    {
        $this->recordedEvents[] = $event;
    }
}

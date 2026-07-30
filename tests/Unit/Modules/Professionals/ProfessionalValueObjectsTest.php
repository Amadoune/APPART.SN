<?php

namespace Tests\Unit\Modules\Professionals;

use Appart\Modules\Professionals\Domain\Exception\InvalidProfessionalValue;
use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentId;
use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentName;
use Appart\Modules\Professionals\Domain\ValueObject\MandateId;
use Appart\Modules\Professionals\Domain\ValueObject\MandateRole;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalName;
use Appart\Modules\Professionals\Domain\ValueObject\RegistrationNumber;
use Appart\Modules\Professionals\Domain\ValueObject\RepresentativeId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProfessionalValueObjectsTest extends TestCase
{
    public function test_names_and_registration_are_normalized(): void
    {
        self::assertSame('Agence Horizon', ProfessionalName::fromString(' Agence   Horizon ')->value);
        self::assertSame('Dakar Centre', EstablishmentName::fromString(' Dakar   Centre ')->value);
        self::assertSame('SN-NINEA/12345', RegistrationNumber::fromString(' sn-ninea/12345 ')->value);
    }

    #[DataProvider('invalidValues')]
    public function test_invalid_values_are_rejected(string $type, string $value): void
    {
        $this->expectException(InvalidProfessionalValue::class);
        match ($type) {
            'professional' => ProfessionalId::fromString($value), 'establishment' => EstablishmentId::fromString($value), 'mandate' => MandateId::fromString($value),
            'registration' => RegistrationNumber::fromString($value), 'professional_name' => ProfessionalName::fromString($value), 'establishment_name' => EstablishmentName::fromString($value),
            'representative' => RepresentativeId::fromString($value), 'role' => MandateRole::fromString($value),
        };
    }

    public static function invalidValues(): array
    {
        return [['professional', 'x'], ['establishment', 'x'], ['mandate', 'x'], ['registration', 'x'], ['professional_name', ''], ['establishment_name', ''], ['representative', '?'], ['role', 'X']];
    }
}

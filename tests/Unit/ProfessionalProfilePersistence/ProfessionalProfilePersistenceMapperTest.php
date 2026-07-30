<?php

namespace Tests\Unit\ProfessionalProfilePersistence;

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalPublicPortfolioState;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalPublicProfileState;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalVerificationState;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\PublicProfileVisibility;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\VerificationDisposition;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalProfilePersistenceMapper;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ProfessionalProfilePersistenceMapperTest extends TestCase
{
    public function test_states_are_bijective_and_empty_contacts_remain_a_json_object(): void
    {
        $mapper = new ProfessionalProfilePersistenceMapper;
        $profile = new ProfessionalPublicProfileState($this->id(1), PublicProfileVisibility::Draft, 'Agency', '', [], [], [], [], 1, 1, 'profile-v1', $this->id(2), str_repeat('a', 64), $this->at());
        $parameters = $mapper->profileParameters($profile);
        self::assertSame('{}', $parameters['public_contacts']);
        self::assertEquals($profile, $mapper->profileState($this->profileRow($parameters)));

        $verification = new ProfessionalVerificationState($this->id(1), VerificationDisposition::Pending, ['proof:opaque'], 'verification-v1', null, null, 0, 1, $this->id(3), str_repeat('b', 64), $this->at());
        self::assertEquals($verification, $mapper->verificationState($this->verificationRow($mapper->verificationParameters($verification))));

        $portfolio = new ProfessionalPublicPortfolioState($this->id(1), [$this->id(4)], 10, 1, $this->id(5), str_repeat('c', 64), $this->at());
        self::assertEquals($portfolio, $mapper->portfolioState($this->portfolioRow($mapper->portfolioParameters($portfolio))));
    }

    /**
     * @param  array<string, mixed>  $p
     * @return array<string, mixed>
     */
    private function profileRow(array $p): array
    {
        return [...$p, 'last_intent_id' => $p['intent_id'], 'last_intent_checksum' => $p['intent_checksum']];
    }

    /**
     * @param  array<string, mixed>  $p
     * @return array<string, mixed>
     */
    private function verificationRow(array $p): array
    {
        return [...$p, 'last_intent_id' => $p['intent_id'], 'last_intent_checksum' => $p['intent_checksum']];
    }

    /**
     * @param  array<string, mixed>  $p
     * @return array<string, mixed>
     */
    private function portfolioRow(array $p): array
    {
        return [...$p, 'last_intent_id' => $p['intent_id'], 'last_intent_checksum' => $p['intent_checksum']];
    }

    private function id(int $suffix): string
    {
        return sprintf('61000000-0000-4000-8000-%012d', $suffix);
    }

    private function at(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-28T12:00:00+00:00');
    }
}

<?php

namespace Tests\Unit\Modules\IdentityAccess;

use Appart\Modules\IdentityAccess\Domain\Exception\InvalidIdentityValue;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\EmailAddress;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PersonName;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PhoneNumber;
use Appart\Modules\IdentityAccess\Domain\ValueObject\RoleId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IdentityValueObjectsTest extends TestCase
{
    public function test_email_and_phone_are_canonicalized(): void
    {
        self::assertSame('contact@appart.sn', EmailAddress::fromString(' Contact@APPART.SN ')->value);
        self::assertSame('+221771234567', PhoneNumber::fromString('+221 77 123 45 67')->value);
    }

    #[DataProvider('invalidValues')]
    public function test_invalid_identity_values_are_rejected(string $type, string $value): void
    {
        $this->expectException(InvalidIdentityValue::class);
        match ($type) {
            'id' => AccountId::fromString($value),
            'email' => EmailAddress::fromString($value),
            'phone' => PhoneNumber::fromString($value),
            'hash' => PasswordHash::fromString($value),
            'name' => PersonName::fromString($value),
            'token' => VerificationToken::forChannel(VerificationChannel::Email, $value),
            'role' => RoleId::fromString($value),
        };
    }

    public function test_verification_tokens_compare_in_constant_time_semantics(): void
    {
        $token = VerificationToken::forChannel(VerificationChannel::Email, str_repeat('a', 40));
        self::assertTrue($token->matches(VerificationToken::forChannel(VerificationChannel::Email, str_repeat('a', 40))));
        self::assertFalse($token->matches(VerificationToken::forChannel(VerificationChannel::Email, str_repeat('b', 40))));
    }

    public function test_password_hash_and_token_cannot_be_serialized(): void
    {
        $hash = PasswordHash::fromString('$generic$v=1$salt-value$'.str_repeat('x', 40));
        $this->expectException(\LogicException::class);
        serialize($hash);
    }

    public function test_verification_token_cannot_be_serialized(): void
    {
        $token = VerificationToken::forChannel(VerificationChannel::Email, str_repeat('a', 40));
        $this->expectException(\LogicException::class);
        serialize($token);
    }

    public static function invalidValues(): array
    {
        return [
            ['id', 'account'], ['email', 'bad'], ['phone', '771234567'], ['hash', 'plaintext'],
            ['name', 'A'], ['token', 'short'], ['role', 'A'], ['hash', str_repeat('a', 40)],
        ];
    }
}

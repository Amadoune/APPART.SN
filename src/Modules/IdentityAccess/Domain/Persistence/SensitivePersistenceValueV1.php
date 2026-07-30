<?php

namespace Appart\Modules\IdentityAccess\Domain\Persistence;

use JsonSerializable;
use LogicException;
use SensitiveParameter;

final readonly class SensitivePersistenceValueV1 implements JsonSerializable
{
    private function __construct(#[SensitiveParameter] private string $secret)
    {
        if ($secret === '') {
            throw new LogicException('A persistence secret cannot be empty.');
        }
    }

    public static function fromSecret(#[SensitiveParameter] string $secret): self
    {
        return new self($secret);
    }

    public function revealForPersistence(): string
    {
        return $this->secret;
    }

    /** @throws LogicException */
    public function __serialize(): array
    {
        throw new LogicException('Persistence secrets cannot be serialized.');
    }

    /** @throws LogicException */
    public function jsonSerialize(): never
    {
        throw new LogicException('Persistence secrets cannot be JSON serialized.');
    }

    public function __debugInfo(): array
    {
        return ['secret' => '[REDACTED]'];
    }
}

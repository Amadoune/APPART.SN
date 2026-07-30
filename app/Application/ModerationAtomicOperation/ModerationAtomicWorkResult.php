<?php

namespace App\Application\ModerationAtomicOperation;

final readonly class ModerationAtomicWorkResult
{
    public function __construct(public ModerationAtomicDecision $decision, public mixed $value = null) {}

    public static function commit(mixed $value = null): self
    {
        return new self(ModerationAtomicDecision::Commit, $value);
    }

    public static function rollback(mixed $value = null): self
    {
        return new self(ModerationAtomicDecision::Rollback, $value);
    }
}

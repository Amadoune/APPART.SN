<?php

namespace Appart\Modules\Professionals\Domain\Exception;

final class ProfessionalViolation extends ProfessionalsException
{
    public static function establishmentAlreadyExists(): self
    {
        return new self('The establishment already belongs to this professional.');
    }

    public static function establishmentNotFound(): self
    {
        return new self('The establishment does not belong to this professional.');
    }

    public static function establishmentAlreadyRemoved(): self
    {
        return new self('The establishment is already removed.');
    }

    public static function establishmentHasActiveMandates(): self
    {
        return new self('An establishment with active mandates cannot be removed.');
    }

    public static function duplicateActiveMandate(): self
    {
        return new self('This representative already has an active mandate for this establishment.');
    }

    public static function mandateNotFound(): self
    {
        return new self('The mandate does not belong to this professional.');
    }

    public static function mandateAlreadyRevoked(): self
    {
        return new self('The mandate is already revoked.');
    }

    public static function suspendedProfessional(): self
    {
        return new self('A suspended professional cannot receive a new mandate.');
    }

    public static function alreadySuspended(): self
    {
        return new self('The professional is already suspended.');
    }

    public static function notSuspended(): self
    {
        return new self('The professional is not suspended.');
    }
}

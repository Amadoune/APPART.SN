<?php

namespace Appart\Modules\AdministrationAudit\Domain\Exception;

final class AdministrativeActionViolation extends AdministrationAuditException
{
    public static function missingReason(): self
    {
        return new self('An audit reason is required.');
    }

    public static function invalidState(): self
    {
        return new self('The administrative action is not in the required state.');
    }

    public static function selfApproval(): self
    {
        return new self('The author cannot approve their own action.');
    }

    public static function selfRejection(): self
    {
        return new self('The author cannot review their own action.');
    }

    public static function approvalNotRequired(): self
    {
        return new self('This action does not require four-eyes approval.');
    }

    public static function reasonAlreadyPresent(): self
    {
        return new self('The reason has already been recorded.');
    }
}

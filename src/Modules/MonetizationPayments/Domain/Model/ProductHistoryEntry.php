<?php

namespace Appart\Modules\MonetizationPayments\Domain\Model;

final readonly class ProductHistoryEntry
{
    public function __construct(public string $action, public int $version) {}
}

<?php

namespace Appart\Modules\RealEstateCatalog\Application\Promotion;

final readonly class PromoteAuthoredPropertyResult
{
    public function __construct(public PromoteAuthoredPropertyStatus $status) {}
}

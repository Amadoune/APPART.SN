<?php

namespace Appart\Modules\Geography\Application\PlaceMergeContext;

final readonly class PlaceMergeContextInspection
{
    public function __construct(
        public PlaceMergeContextV1 $context,
        public int $resultingSourceVersion,
    ) {
        if ($resultingSourceVersion !== $context->expectedSourceVersion->value + 1) {
            throw new \InvalidArgumentException('The inspected source version must immediately follow the expected source version.');
        }
    }
}

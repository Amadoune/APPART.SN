<?php

namespace Appart\Modules\Media\Application\MediaIngestionEventIntegration\Contract;

interface MediaIngestionAtomicDeliveryTransaction
{
    /**
     * @template T
     *
     * @param  callable(): T  $operation
     * @return T
     */
    public function execute(callable $operation): mixed;
}

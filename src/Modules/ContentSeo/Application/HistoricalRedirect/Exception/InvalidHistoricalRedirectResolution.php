<?php

namespace Appart\Modules\ContentSeo\Application\HistoricalRedirect\Exception;

use LogicException;

final class InvalidHistoricalRedirectResolution extends LogicException
{
    public static function destinationEqualsSource(): self
    {
        return new self('A resolved historical redirect destination cannot equal its source.');
    }
}

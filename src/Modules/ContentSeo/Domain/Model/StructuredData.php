<?php

namespace Appart\Modules\ContentSeo\Domain\Model;

use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;
use Appart\Modules\ContentSeo\Domain\ValueObject\StructuredDataType;

final readonly class StructuredData
{
    /** @var array<string, string> */
    public array $facts;

    /** @param array<string, string> $facts */
    public function __construct(public StructuredDataType $type, array $facts)
    {
        $required = match ($type) {
            StructuredDataType::RealEstateListing => ['addressLocality', 'category', 'name', 'url'],
        };
        $keys = array_keys($facts);
        sort($keys);
        if ($keys !== $required) {
            throw InvalidSeoValue::field('structured_data');
        }
        foreach ($facts as $value) {
            if (trim($value) === '') {
                throw InvalidSeoValue::field('structured_data');
            }
        }
        ksort($facts);
        $this->facts = $facts;
    }
}

<?php

namespace Appart\Modules\ContentSeo\Domain\Model;

use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;

final readonly class BreadcrumbItem
{
    public function __construct(public string $label, public CanonicalUrl $url)
    {
        $label = trim($label);
        if ($label === '' || $label !== $this->label) {
            throw InvalidSeoValue::field('breadcrumb_label');
        }
    }
}

<?php

namespace Appart\Modules\SearchDiscovery\Domain\ValueObject;

enum SourceKind: string
{
    case Listing = 'listing';
    case Property = 'property';
    case Media = 'media';
}

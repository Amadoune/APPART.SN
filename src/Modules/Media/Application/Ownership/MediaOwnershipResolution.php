<?php

namespace Appart\Modules\Media\Application\Ownership;

enum MediaOwnershipResolution: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Ambiguous = 'ambiguous';
}

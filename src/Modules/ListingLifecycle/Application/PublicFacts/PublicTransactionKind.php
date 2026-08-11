<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicFacts;

enum PublicTransactionKind: string
{
    case Sale = 'sale';
    case Rent = 'rent';
}

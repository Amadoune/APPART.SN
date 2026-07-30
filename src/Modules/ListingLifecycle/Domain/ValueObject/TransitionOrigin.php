<?php

namespace Appart\Modules\ListingLifecycle\Domain\ValueObject;

enum TransitionOrigin: string
{
    case Advertiser = 'advertiser';
    case Moderation = 'moderation';
    case System = 'system';
    case Administration = 'administration';
}

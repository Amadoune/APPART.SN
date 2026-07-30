<?php

namespace Appart\Modules\ListingLifecycle\Application\ModerationBoundary;

enum ListingModerationActionV1: string
{
    case Suspend = 'suspend';
    case RequestChanges = 'request_changes';
    case Reject = 'reject';
    case Archive = 'archive';
}

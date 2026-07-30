<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleContext;

enum MediaPrimaryTransitionDisposition: string
{
    case NotPrimary = 'not_primary';
    case ReplacementSelected = 'replacement_selected';
}

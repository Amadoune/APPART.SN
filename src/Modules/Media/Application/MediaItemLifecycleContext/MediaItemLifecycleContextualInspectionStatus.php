<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleContext;

enum MediaItemLifecycleContextualInspectionStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}

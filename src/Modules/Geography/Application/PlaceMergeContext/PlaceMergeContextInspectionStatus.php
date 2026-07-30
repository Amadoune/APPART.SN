<?php

namespace Appart\Modules\Geography\Application\PlaceMergeContext;

enum PlaceMergeContextInspectionStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}

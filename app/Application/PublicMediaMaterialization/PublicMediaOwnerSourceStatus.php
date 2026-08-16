<?php

namespace App\Application\PublicMediaMaterialization;

enum PublicMediaOwnerSourceStatus
{
    case Ready;
    case Missing;
    case NotReady;
    case Corrupted;
    case DependencyUnavailable;
}

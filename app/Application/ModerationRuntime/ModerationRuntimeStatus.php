<?php

namespace App\Application\ModerationRuntime;

enum ModerationRuntimeStatus: string
{
    case Healthy = 'Healthy';
    case Unavailable = 'Unavailable';
}

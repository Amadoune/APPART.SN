<?php

namespace App\Application\PublicProjectionRetry;

enum PublicProjectionReplayScope: string
{
    case Message = 'message';
    case Aggregate = 'aggregate';
    case Module = 'module';
    case Range = 'range';
    case HighWatermark = 'high_watermark';
}

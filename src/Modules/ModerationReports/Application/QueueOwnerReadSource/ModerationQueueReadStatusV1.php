<?php

namespace Appart\Modules\ModerationReports\Application\QueueOwnerReadSource;

enum ModerationQueueReadStatusV1: string
{
    case PageAvailable = 'page_available';
    case Empty = 'empty';
    case InvalidCursor = 'invalid_cursor';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}

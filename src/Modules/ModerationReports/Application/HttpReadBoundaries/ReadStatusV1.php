<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries;

enum ReadStatusV1: string
{
    case Visible = 'visible';
    case NotVisible = 'not_visible';
    case Available = 'available';
    case Empty = 'empty';
    case ForbiddenActor = 'forbidden_actor';
    case InvalidCursor = 'invalid_cursor';
    case QueueUnavailable = 'queue_unavailable';
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}

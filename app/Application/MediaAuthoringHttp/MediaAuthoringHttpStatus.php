<?php

namespace App\Application\MediaAuthoringHttp;

enum MediaAuthoringHttpStatus: string
{
    case Created = 'created';
    case Available = 'available';
    case Empty = 'empty';
    case NotFoundOrForbidden = 'not_found_or_forbidden';
    case Invalid = 'invalid';
    case Conflict = 'conflict';
    case Unavailable = 'unavailable';
}

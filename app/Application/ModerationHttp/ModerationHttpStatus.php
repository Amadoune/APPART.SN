<?php

namespace App\Application\ModerationHttp;

enum ModerationHttpStatus: string
{
    case Succeeded = 'succeeded';
    case Created = 'created';
    case NotFound = 'not_found';
    case Forbidden = 'forbidden';
    case Conflict = 'conflict';
    case Invalid = 'invalid';
    case Unavailable = 'unavailable';
}

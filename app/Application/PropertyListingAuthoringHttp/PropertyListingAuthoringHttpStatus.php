<?php

namespace App\Application\PropertyListingAuthoringHttp;

enum PropertyListingAuthoringHttpStatus: string
{
    case Succeeded = 'succeeded';
    case Created = 'created';
    case NotFoundOrForbidden = 'not_found';
    case Invalid = 'invalid';
    case Conflict = 'conflict';
    case Unavailable = 'unavailable';
}

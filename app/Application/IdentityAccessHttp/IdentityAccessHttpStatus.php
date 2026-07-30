<?php

namespace App\Application\IdentityAccessHttp;

enum IdentityAccessHttpStatus: string
{
    case Succeeded = 'succeeded';
    case Accepted = 'accepted';
    case GenericFailure = 'generic_failure';
    case Forbidden = 'forbidden';
    case Conflict = 'conflict';
    case Unavailable = 'unavailable';
}

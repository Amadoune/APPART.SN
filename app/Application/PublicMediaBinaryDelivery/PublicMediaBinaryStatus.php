<?php

namespace App\Application\PublicMediaBinaryDelivery;

enum PublicMediaBinaryStatus: string
{
    case Found = 'found';
    case NotFound = 'not_found';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}

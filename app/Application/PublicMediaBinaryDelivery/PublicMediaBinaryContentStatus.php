<?php

namespace App\Application\PublicMediaBinaryDelivery;

enum PublicMediaBinaryContentStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}

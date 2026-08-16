<?php

namespace App\Application\PublicMediaBinaryDelivery;

enum PublicMediaBinarySourceStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}

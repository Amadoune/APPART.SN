<?php

namespace App\Application\PublicMediaSource;

enum PublicMediaReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}

<?php

namespace App\Application\ActiveGenerationReader;

enum ActiveGenerationReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}

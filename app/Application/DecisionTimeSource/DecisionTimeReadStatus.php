<?php

namespace App\Application\DecisionTimeSource;

enum DecisionTimeReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}

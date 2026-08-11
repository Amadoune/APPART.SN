<?php

namespace App\Application\OwnerDashboard;

enum OwnerDashboardReadStatus: string
{
    case Available = 'available';
    case Empty = 'empty';
    case Unavailable = 'unavailable';
}

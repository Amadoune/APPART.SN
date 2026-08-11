<?php

namespace App\Http\Controllers;

use App\Application\OwnerDashboard\Contract\OwnerDashboardReadSourceV1;
use Illuminate\Contracts\View\View;

final class OwnerDashboardController extends Controller
{
    public function __construct(private readonly OwnerDashboardReadSourceV1 $source) {}

    public function __invoke(): View
    {
        return view('owner-dashboard', ['dashboard' => $this->source->read()]);
    }
}

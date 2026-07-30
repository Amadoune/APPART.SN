<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

final class AuthoringWorkspaceController extends Controller
{
    public function __invoke(): View
    {
        return view('authoring-workspace');
    }
}

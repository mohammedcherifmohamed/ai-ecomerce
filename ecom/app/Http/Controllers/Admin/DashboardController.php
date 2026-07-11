<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}

    public function __invoke()
    {
        $stats = $this->dashboardService->getStats();

        return view('admin.dashboard', compact('stats'));
    }
}

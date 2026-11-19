<?php

namespace App\Http\Controllers;

use App\Services\SystemHealthService;
use Inertia\Inertia;

class SystemHealthController extends Controller
{
    public function __invoke(SystemHealthService $service)
    {
        return Inertia::render('SystemHealth', [
            'health' => $service->check(),
        ]);
    }
}

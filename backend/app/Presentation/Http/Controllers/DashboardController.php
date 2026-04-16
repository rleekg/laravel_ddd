<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Balance\Queries\DashboardQueryHandler;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

final class DashboardController extends Controller
{
    public function index(): View
    {
        return view('app');
    }

    public function dashboard(DashboardQueryHandler $handler): JsonResponse
    {
        $data = $handler->handle((int) Auth::id());

        return response()->json($data);
    }
}

<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Balance\Queries\OperationsQueryHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

final class OperationsController extends Controller
{
    public function index(Request $request, OperationsQueryHandler $handler): JsonResponse
    {
        $request->validate([
            'sort' => 'in:asc,desc',
            'page' => 'integer|min:1',
        ]);

        $sortRaw = $request->input('sort', 'desc');
        $sort = is_string($sortRaw) ? $sortRaw : 'desc';
        $searchRaw = $request->input('search');
        $search = is_string($searchRaw) ? $searchRaw : null;
        $data = $handler->handle(
            (int) Auth::id(),
            $sort,
            $search,
            $request->integer('page', 1),
        );

        return response()->json($data);
    }
}

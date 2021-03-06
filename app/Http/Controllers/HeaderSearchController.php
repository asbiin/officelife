<?php

namespace App\Http\Controllers;

use App\Models\Company\Team;
use Illuminate\Http\Request;
use App\Helpers\InstanceHelper;
use App\Models\Company\Employee;
use Illuminate\Http\JsonResponse;
use App\Http\ViewHelpers\Company\HeaderSearchViewHelper;

class HeaderSearchController extends Controller
{
    /**
     * Perform search of an employee from the header.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function employees(Request $request): JsonResponse
    {
        $search = $request->input('searchTerm');
        $employees = Employee::search($search, InstanceHelper::getLoggedCompany()->id, 10, 'created_at desc', 'and locked = false');

        return response()->json([
            'data' => HeaderSearchViewHelper::employees($employees),
        ], 200);
    }

    /**
     * Perform search of an team from the header.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function teams(Request $request): JsonResponse
    {
        $search = $request->input('searchTerm');
        $teams = Team::search($search, InstanceHelper::getLoggedCompany()->id, 10, 'created_at desc');

        return response()->json([
            'data' => HeaderSearchViewHelper::teams($teams),
        ], 200);
    }
}

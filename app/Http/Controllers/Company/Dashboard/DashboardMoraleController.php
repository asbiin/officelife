<?php

namespace App\Http\Controllers\Company\Dashboard;

use Illuminate\Http\Request;
use App\Helpers\InstanceHelper;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Company\Employee\Morale\LogMorale;

class DashboardMoraleController extends Controller
{
    /**
     * Create a morale log.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $company = InstanceHelper::getLoggedCompany();
        $employee = InstanceHelper::getLoggedEmployee();

        $request = [
            'author_id' => $employee->id,
            'employee_id' => $employee->id,
            'company_id' => $company->id,
            'emotion' => $request->input('emotion'),
            'comment' => $request->input('comment'),
        ];

        (new LogMorale)->execute($request);

        $employee->refresh();

        return response()->json([
            'data' => true,
        ], 200);
    }
}

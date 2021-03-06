<?php

namespace App\Http\Controllers\Company\Employee;

use Illuminate\Http\Request;
use App\Helpers\InstanceHelper;
use App\Models\Company\Employee;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Company\Employee\Position\AssignPositionToEmployee;
use App\Services\Company\Employee\Position\RemovePositionFromEmployee;

class EmployeePositionController extends Controller
{
    /**
     * Assign a position to the given employee.
     *
     * @param Request $request
     * @param int $companyId
     * @param int $employeeId
     * @return JsonResponse
     */
    public function store(Request $request, int $companyId, int $employeeId): JsonResponse
    {
        $loggedEmployee = InstanceHelper::getLoggedEmployee();

        $request = [
            'company_id' => $companyId,
            'author_id' => $loggedEmployee->id,
            'employee_id' => $employeeId,
            'position_id' => $request->input('id'),
        ];

        $employee = (new AssignPositionToEmployee)->execute($request);

        return response()->json([
            'data' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'avatar' => $employee->avatar,
                'position' => ($employee->position) ? $employee->position : null,
            ],
        ], 200);
    }

    /**
     * Remove the position for the given employee.
     *
     * @param Request $request
     * @param int $companyId
     * @param int $employeeId
     * @return JsonResponse
     */
    public function destroy(Request $request, int $companyId, int $employeeId): JsonResponse
    {
        $loggedEmployee = InstanceHelper::getLoggedEmployee();

        $request = [
            'company_id' => $companyId,
            'author_id' => $loggedEmployee->id,
            'employee_id' => $employeeId,
        ];

        $employee = (new RemovePositionFromEmployee)->execute($request);

        return response()->json([
            'data' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'avatar' => $employee->avatar,
                'position' => null,
            ],
        ], 200);
    }
}

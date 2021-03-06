<?php

namespace App\Services\Company\Adminland\Expense;

use Carbon\Carbon;
use App\Jobs\NotifyEmployee;
use App\Jobs\LogAccountAudit;
use App\Services\BaseService;
use App\Jobs\LogEmployeeAudit;
use App\Models\Company\Employee;

class AllowEmployeeToManageExpenses extends BaseService
{
    private Employee $employee;

    private array $data;

    /**
     * Get the validation rules that apply to the service.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'company_id' => 'required|integer|exists:companies,id',
            'author_id' => 'required|integer|exists:employees,id',
            'employee_id' => 'required|integer|exists:employees,id',
        ];
    }

    /**
     * Allows an employee to manage all the the expenses in the company.
     * That means the employee will have access to the accounting tab on the
     * dashboard to manage all other employee's expenses.
     *
     * @param array $data
     *
     * @return Employee
     */
    public function execute(array $data): Employee
    {
        $this->data = $data;

        $this->validateRules($data);

        $this->author($data['author_id'])
            ->inCompany($data['company_id'])
            ->asAtLeastHR()
            ->canExecuteService();

        $this->employee = $this->validateEmployeeBelongsToCompany($data);

        $this->employee->can_manage_expenses = true;
        $this->employee->save();

        $this->log();

        return $this->employee;
    }

    private function log(): void
    {
        LogAccountAudit::dispatch([
            'company_id' => $this->data['company_id'],
            'action' => 'employee_allowed_to_manage_expenses',
            'author_id' => $this->author->id,
            'author_name' => $this->author->name,
            'audited_at' => Carbon::now(),
            'objects' => json_encode([
                'employee_id' => $this->employee->id,
                'employee_name' => $this->employee->name,
            ]),
        ])->onQueue('low');

        LogEmployeeAudit::dispatch([
            'employee_id' => $this->employee->id,
            'action' => 'employee_allowed_to_manage_expenses',
            'author_id' => $this->author->id,
            'author_name' => $this->author->name,
            'audited_at' => Carbon::now(),
            'objects' => json_encode([]),
            'is_dummy' => $this->valueOrFalse($this->data, 'is_dummy'),
        ])->onQueue('low');

        NotifyEmployee::dispatch([
            'employee_id' => $this->employee->id,
            'action' => 'employee_allowed_to_manage_expenses',
            'objects' => json_encode([]),
        ])->onQueue('low');
    }
}

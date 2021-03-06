<?php

namespace App\Services\Company\Employee\Team;

use Carbon\Carbon;
use App\Jobs\LogTeamAudit;
use App\Jobs\NotifyEmployee;
use App\Models\Company\Team;
use App\Jobs\LogAccountAudit;
use App\Services\BaseService;
use App\Jobs\LogEmployeeAudit;
use App\Models\Company\Employee;

class RemoveEmployeeFromTeam extends BaseService
{
    private Employee $employee;

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
            'team_id' => 'required|integer|exists:teams,id',
            'is_dummy' => 'nullable|boolean',
        ];
    }

    /**
     * Remove an employee from a team.
     *
     * @param array $data
     *
     * @return Employee
     */
    public function execute(array $data): Employee
    {
        $this->validateRules($data);

        $this->author($data['author_id'])
            ->inCompany($data['company_id'])
            ->asAtLeastHR()
            ->canExecuteService();

        $this->employee = $this->validateEmployeeBelongsToCompany($data);

        $team = $this->validateTeamBelongsToCompany($data);

        $team->employees()->detach(
            $data['employee_id'],
            ['company_id' => $data['company_id']]
        );

        $this->addNotification($team);

        $this->log($data, $team);

        $this->employee->refresh();

        return $this->employee;
    }

    /**
     * Add a notification in the UI for the employee that is added to the team.
     *
     * @param Team $team
     */
    private function addNotification(Team $team): void
    {
        NotifyEmployee::dispatch([
            'employee_id' => $this->employee->id,
            'action' => 'employee_removed_from_team',
            'objects' => json_encode([
                'team_name' => $team->name,
            ]),
        ])->onQueue('low');
    }

    /**
     * Add the logs in the different audit logs.
     *
     * @param array $data
     * @param Team  $team
     */
    private function log(array $data, Team $team): void
    {
        $dataToLog = [
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->name,
            'team_id' => $team->id,
            'team_name' => $team->name,
        ];

        LogAccountAudit::dispatch([
            'company_id' => $data['company_id'],
            'action' => 'employee_removed_from_team',
            'author_id' => $this->author->id,
            'author_name' => $this->author->name,
            'audited_at' => Carbon::now(),
            'objects' => json_encode($dataToLog),
            'is_dummy' => $this->valueOrFalse($data, 'is_dummy'),
        ])->onQueue('low');

        LogTeamAudit::dispatch([
            'team_id' => $team->id,
            'action' => 'employee_removed_from_team',
            'author_id' => $this->author->id,
            'author_name' => $this->author->name,
            'audited_at' => Carbon::now(),
            'objects' => json_encode($dataToLog),
            'is_dummy' => $this->valueOrFalse($data, 'is_dummy'),
        ])->onQueue('low');

        LogEmployeeAudit::dispatch([
            'employee_id' => $this->employee->id,
            'action' => 'employee_removed_from_team',
            'author_id' => $this->author->id,
            'author_name' => $this->author->name,
            'audited_at' => Carbon::now(),
            'objects' => json_encode($dataToLog),
            'is_dummy' => $this->valueOrFalse($data, 'is_dummy'),
        ])->onQueue('low');
    }
}

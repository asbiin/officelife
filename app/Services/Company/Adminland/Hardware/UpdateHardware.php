<?php

namespace App\Services\Company\Adminland\Hardware;

use Carbon\Carbon;
use App\Jobs\LogAccountAudit;
use App\Services\BaseService;
use App\Models\Company\Hardware;

class UpdateHardware extends BaseService
{
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
            'hardware_id' => 'required|integer|exists:hardware,id',
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'is_dummy' => 'nullable|boolean',
        ];
    }

    /**
     * Update a hardware.
     *
     * @param array $data
     *
     * @return Hardware
     */
    public function execute(array $data): Hardware
    {
        $this->validateRules($data);

        $this->author($data['author_id'])
            ->inCompany($data['company_id'])
            ->asAtLeastHR()
            ->canExecuteService();

        $hardware = Hardware::where('company_id', $data['company_id'])
            ->findOrFail($data['hardware_id']);

        $oldName = $hardware->name;

        $hardware->name = $data['name'];
        $hardware->serial_number = $this->valueOrNull($data, 'serial_number');
        $hardware->save();

        LogAccountAudit::dispatch([
            'company_id' => $data['company_id'],
            'action' => 'hardware_updated',
            'author_id' => $this->author->id,
            'author_name' => $this->author->name,
            'audited_at' => Carbon::now(),
            'objects' => json_encode([
                'hardware_id' => $hardware->id,
                'hardware_name' => $hardware->name,
                'hardware_old_name' => $oldName,
            ]),
            'is_dummy' => $this->valueOrFalse($data, 'is_dummy'),
        ])->onQueue('low');

        $hardware->refresh();

        return $hardware;
    }
}

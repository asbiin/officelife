<?php

namespace App\Services\Company\Adminland\CompanyNews;

use Carbon\Carbon;
use App\Jobs\LogAccountAudit;
use App\Services\BaseService;
use App\Models\Company\CompanyNews;

class CreateCompanyNews extends BaseService
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
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:65535',
            'created_at' => 'nullable|date_format:Y-m-d H:i:s',
            'is_dummy' => 'nullable|boolean',
        ];
    }

    /**
     * Create a company news.
     *
     * @param array $data
     *
     * @return CompanyNews
     */
    public function execute(array $data): CompanyNews
    {
        $this->validateRules($data);

        $this->author($data['author_id'])
            ->inCompany($data['company_id'])
            ->asAtLeastHR()
            ->canExecuteService();

        $news = CompanyNews::create([
            'company_id' => $data['company_id'],
            'author_id' => $data['author_id'],
            'author_name' => $this->author->name,
            'title' => $data['title'],
            'content' => $data['content'],
            'is_dummy' => $this->valueOrFalse($data, 'is_dummy'),
        ]);

        if (! empty($data['created_at'])) {
            $news->created_at = $data['created_at'];
            $news->save();
        }

        LogAccountAudit::dispatch([
            'company_id' => $data['company_id'],
            'action' => 'company_news_created',
            'author_id' => $this->author->id,
            'author_name' => $this->author->name,
            'audited_at' => Carbon::now(),
            'objects' => json_encode([
                'company_news_id' => $news->id,
                'company_news_title' => $news->title,
            ]),
            'is_dummy' => $this->valueOrFalse($data, 'is_dummy'),
        ])->onQueue('low');

        return $news;
    }
}

<?php

namespace App\Services\Company\Team\TeamNews;

use Carbon\Carbon;
use App\Jobs\LogTeamAudit;
use App\Models\Company\Team;
use App\Jobs\LogAccountAudit;
use App\Services\BaseService;
use App\Models\Company\TeamNews;

class CreateTeamNews extends BaseService
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
            'team_id' => 'required|integer|exists:teams,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:65535',
            'created_at' => 'nullable|date_format:Y-m-d H:i:s',
            'is_dummy' => 'nullable|boolean',
        ];
    }

    /**
     * Create a team news.
     *
     * @param array $data
     * @return TeamNews
     */
    public function execute(array $data): TeamNews
    {
        $this->validate($data);

        $author = $this->validatePermissions(
            $data['author_id'],
            $data['company_id'],
            config('officelife.authorizations.user')
        );

        $team = Team::where('company_id', $data['company_id'])
            ->findOrFail($data['team_id']);

        $news = TeamNews::create([
            'team_id' => $team->id,
            'author_id' => $data['author_id'],
            'author_name' => $author->name,
            'title' => $data['title'],
            'content' => $data['content'],
            'is_dummy' => $this->valueOrFalse($data, 'is_dummy'),
        ]);

        if (!empty($data['created_at'])) {
            $news->created_at = $data['created_at'];
            $news->save();
        }

        LogAccountAudit::dispatch([
            'company_id' => $data['company_id'],
            'action' => 'team_news_created',
            'author_id' => $author->id,
            'author_name' => $author->name,
            'audited_at' => Carbon::now(),
            'objects' => json_encode([
                'team_id' => $team->id,
                'team_name' => $team->name,
                'team_news_id' => $news->id,
                'team_news_title' => $news->title,
            ]),
            'is_dummy' => $this->valueOrFalse($data, 'is_dummy'),
        ])->onQueue('low');

        LogTeamAudit::dispatch([
            'team_id' => $team->id,
            'action' => 'team_news_created',
            'author_id' => $author->id,
            'author_name' => $author->name,
            'audited_at' => Carbon::now(),
            'objects' => json_encode([
                'team_news_id' => $news->id,
                'team_news_title' => $news->title,
            ]),
            'is_dummy' => $this->valueOrFalse($data, 'is_dummy'),
        ])->onQueue('low');

        return $news;
    }
}
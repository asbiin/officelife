<?php

namespace Tests\Unit\ViewHelpers\Employee;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Company\Employee;
use App\Models\Company\OneOnOneNote;
use App\Models\Company\OneOnOneEntry;
use App\Models\Company\OneOnOneActionItem;
use App\Models\Company\OneOnOneTalkingPoint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\ViewHelpers\Employee\EmployeeOneOnOneViewHelper;

class EmployeeOneOnOneViewHelperTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_gets_an_array_of_statistics_about_the_ones_on_ones(): void
    {
        Carbon::setTestNow(Carbon::create(2018, 1, 1));

        $michael = factory(Employee::class)->create([]);

        factory(OneOnOneEntry::class)->create([
            'manager_id' => $michael->id,
            'happened_at' => '2018-01-01 00:00:00',
        ]);
        factory(OneOnOneEntry::class)->create([
            'manager_id' => $michael->id,
            'happened_at' => '2018-03-01 00:00:00',
        ]);
        factory(OneOnOneEntry::class)->create([
            'manager_id' => $michael->id,
            'happened_at' => '2018-05-01 00:00:00',
        ]);
        // this entry shouldn't be counted as it’s more than 365 days ago
        factory(OneOnOneEntry::class)->create([
            'manager_id' => $michael->id,
            'happened_at' => '2013-05-01 00:00:00',
        ]);

        $this->assertEquals(
            [
                'numberOfOccurrencesThisYear' => 3,
                'averageTimeBetween' => 40,
            ],
            EmployeeOneOnOneViewHelper::stats($michael->oneOnOneEntriesAsManager)
        );
    }

    /** @test */
    public function it_gets_a_collection_of_ones_on_ones(): void
    {
        Carbon::setTestNow(Carbon::create(2019, 1, 1, 7, 0, 0));

        Carbon::setTestNow(Carbon::create(2019, 1, 1, 7, 0, 0));

        $michael = $this->createAdministrator();
        $dwight = $this->createDirectReport($michael);

        $entry2019 = factory(OneOnOneEntry::class)->create([
            'manager_id' => $michael->id,
            'employee_id' => $dwight->id,
            'created_at' => '2019-01-01 01:00:00',
        ]);
        $entry2018 = factory(OneOnOneEntry::class)->create([
            'manager_id' => $michael->id,
            'employee_id' => $dwight->id,
            'created_at' => '2018-01-01 01:00:00',
        ]);
        $entry2017 = factory(OneOnOneEntry::class)->create([
            'manager_id' => $michael->id,
            'employee_id' => $dwight->id,
            'created_at' => '2017-01-01 01:00:00',
        ]);

        $collection = EmployeeOneOnOneViewHelper::list($dwight->oneOnOneEntriesAsEmployee, $dwight);

        $this->assertEquals(3, $collection->count());

        $this->assertEquals(
            [
                0 => [
                    'id' => $entry2019->id,
                    'happened_at' => 'Mar 02, 2020',
                    'number_of_talking_points' => 0,
                    'number_of_action_items' => 0,
                    'number_of_notes' => 0,
                    'manager' => [
                        'id' => $michael->id,
                        'name' => $michael->name,
                        'avatar' => $michael->avatar,
                        'url' => env('APP_URL').'/'.$michael->company_id.'/employees/'.$michael->id,
                    ],
                    'url' => env('APP_URL').'/'.$michael->company_id.'/employees/'.$dwight->id.'/oneonones/'.$entry2019->id,
                ],
                1 => [
                    'id' => $entry2018->id,
                    'happened_at' => 'Mar 02, 2020',
                    'number_of_talking_points' => 0,
                    'number_of_action_items' => 0,
                    'number_of_notes' => 0,
                    'manager' => [
                        'id' => $michael->id,
                        'name' => $michael->name,
                        'avatar' => $michael->avatar,
                        'url' => env('APP_URL').'/'.$michael->company_id.'/employees/'.$michael->id,
                    ],
                    'url' => env('APP_URL').'/'.$michael->company_id.'/employees/'.$dwight->id.'/oneonones/'.$entry2018->id,
                ],
                2 => [
                    'id' => $entry2017->id,
                    'happened_at' => 'Mar 02, 2020',
                    'number_of_talking_points' => 0,
                    'number_of_action_items' => 0,
                    'number_of_notes' => 0,
                    'manager' => [
                        'id' => $michael->id,
                        'name' => $michael->name,
                        'avatar' => $michael->avatar,
                        'url' => env('APP_URL').'/'.$michael->company_id.'/employees/'.$michael->id,
                    ],
                    'url' => env('APP_URL').'/'.$michael->company_id.'/employees/'.$dwight->id.'/oneonones/'.$entry2017->id,
                ],
            ],
            $collection->toArray()
        );
    }

    /** @test */
    public function it_gets_an_array_containing_all_the_information_about_a_one_on_one_entry(): void
    {
        $michael = $this->createAdministrator();
        $dwight = $this->createDirectReport($michael);

        $entry = factory(OneOnOneEntry::class)->create([
            'manager_id' => $michael->id,
            'employee_id' => $dwight->id,
            'happened_at' => '2020-09-09',
        ]);

        $talkingPoint = factory(OneOnOneTalkingPoint::class)->create([
            'one_on_one_entry_id' => $entry->id,
        ]);

        $actionItem = factory(OneOnOneActionItem::class)->create([
            'one_on_one_entry_id' => $entry->id,
        ]);

        $note = factory(OneOnOneNote::class)->create([
            'one_on_one_entry_id' => $entry->id,
        ]);

        $array = EmployeeOneOnOneViewHelper::details($entry);

        $this->assertEquals($entry->id, $array['id']);
        $this->assertEquals('Sep 09, 2020', $array['happened_at']);
        $this->assertEquals(
            [
                'id' => $entry->employee->id,
                'name' => $entry->employee->name,
                'avatar' => $entry->employee->avatar,
                'url' => env('APP_URL').'/'.$michael->company_id.'/employees/'.$entry->employee->id,
            ],
            $array['employee']
        );
        $this->assertEquals(
            [
                'id' => $entry->manager->id,
                'name' => $entry->manager->name,
                'avatar' => $entry->manager->avatar,
                'url' => env('APP_URL').'/'.$michael->company_id.'/employees/'.$entry->manager->id,
            ],
            $array['manager']
        );
        $this->assertEquals(
            [
                0 => [
                    'id' => $talkingPoint->id,
                    'description' => 'what are you doing right now',
                    'checked' => false,
                ],
            ],
            $array['talking_points']->toArray()
        );
        $this->assertEquals(
            [
                0 => [
                    'id' => $actionItem->id,
                    'description' => 'what are you doing right now',
                    'checked' => false,
                ],
            ],
            $array['action_items']->toArray()
        );
        $this->assertEquals(
            [
                0 => [
                    'id' => $note->id,
                    'note' => 'what are you doing right now',
                ],
            ],
            $array['notes']->toArray()
        );
    }
}

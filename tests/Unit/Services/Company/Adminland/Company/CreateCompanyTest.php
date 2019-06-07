<?php

namespace Tests\Unit\Services\Company\Adminland\Company;

use Tests\TestCase;
use App\Models\User\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Services\Company\Adminland\Company\CreateCompany;

class CreateCompanyTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_creates_a_company()
    {
        $author = factory(User::class)->create([]);

        $request = [
            'author_id' => $author->id,
            'name' => 'Dunder Mifflin',
        ];

        $company = (new CreateCompany)->execute($request);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Dunder Mifflin',
        ]);

        $this->assertDatabaseHas('employees', [
            'company_id' => $company->id,
            'user_id' => $author->id,
        ]);
    }

    /** @test */
    public function it_logs_an_action()
    {
        $author = factory(User::class)->create([]);

        $request = [
            'author_id' => $author->id,
            'name' => 'Dunder Mifflin',
        ];

        $company = (new CreateCompany)->execute($request);

        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $company->id,
            'action' => 'account_created',
        ]);
    }

    /** @test */
    public function it_populates_default_positions()
    {
        $author = factory(User::class)->create([]);

        $request = [
            'author_id' => $author->id,
            'name' => 'Dunder Mifflin',
        ];

        $company = (new CreateCompany)->execute($request);

        $numberOfPositions = $company->positions()->count();
        $this->assertEquals(
            4,
            $numberOfPositions
        );
    }

    /** @test */
    public function it_fails_if_wrong_parameters_are_given()
    {
        $author = factory(User::class)->create([]);

        $request = [
            'author_id' => $author->id,
        ];

        $this->expectException(ValidationException::class);
        (new CreateCompany)->execute($request);
    }
}
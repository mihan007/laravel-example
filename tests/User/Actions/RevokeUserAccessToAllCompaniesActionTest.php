<?php

namespace Tests\User\Actions;

use App\Domain\Company\Models\CompanyRoleUser;
use App\Domain\User\Actions\RevokeUserAccessToAllCompaniesAction;
use App\Domain\User\Models\User;
use Database\Factories\Domain\Company\Models\CompanyRoleUserFactory;
use Database\Factories\Domain\User\Models\UserFactory;
use Tests\TestCase;

class RevokeUserAccessToAllCompaniesActionTest extends TestCase
{
    const COUNT = 10;

    private RevokeUserAccessToAllCompaniesAction $revokeUserAccessToAllCompaniesAction;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->truncate(CompanyRoleUser::class, false);
        $this->truncate(User::class);

        $this->user = UserFactory::new()->create();
        CompanyRoleUserFactory::new()->user($this->user)->count(self::COUNT)->create();

        $this->revokeUserAccessToAllCompaniesAction = app(RevokeUserAccessToAllCompaniesAction::class);
    }

    /** @test */
    public function can_delete_account_from_database()
    {
        $this->assertEquals(
            self::COUNT,
            CompanyRoleUser::query()
                ->where('user_id', $this->user->id)
                ->count()
        );

        $this->revokeUserAccessToAllCompaniesAction->execute($this->user);

        $this->assertEquals(
            0,
            CompanyRoleUser::query()
                ->where('user_id', $this->user->id)
                ->count()
        );
    }

    public function tearDown(): void
    {
        $this->truncate(CompanyRoleUser::class, false);
        $this->truncate(User::class);
    }
}

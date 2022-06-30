<?php

namespace Database\Factories\Domain\User\Models;

use App\Domain\Company\Models\CompanyRoleUser;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManagerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $managerRole = RoleFactory::new()->manager()->create();
        $user = User::factory()->create();
        $company = \App\Domain\Company\Models\Company::factory()->create();

        CompanyRoleUser::query()->create(
            [
                'user_id' => $user->id,
                'company_id' => $company->id
            ]
        );

        \App\Domain\Account\Models\AccountUser::create(
            [
                'role' => $managerRole->name,
                'user_id' => $user->id
            ],
        );
        $user->detachRoles($user->roles);
        $user->attachRole(User::ROLE_ACCOUNT_MANAGER_ID);

        return $user->getAttributes();
    }
}

<?php

namespace Database\Factories\Domain\Company\Models;

use App\Domain\Company\Models\Company;
use App\Domain\Company\Models\CompanyRoleUser;
use App\Domain\User\Models\User;
use Database\Factories\Domain\User\Models\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyRoleUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CompanyRoleUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = UserFactory::new()->create();
        $company = CompanyFactory::new()->create();

        return [
            'user_id' => $user->id,
            'company_id' => $company->id
        ];
    }

    public function user(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id
            ];
        });
    }

    public function company(Company $company)
    {
        return $this->state(function (array $attributes) use ($company) {
            return [
                'company_id' => $company->id
            ];
        });
    }
}

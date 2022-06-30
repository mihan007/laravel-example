<?php

namespace Tests;

use App\Domain\Account\Models\AccountUser;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function signIn($user = null, $roleName = null, $account = null, $accountUser = null)
    {
        $user = $user ?: User::factory()->create();
        $user->fresh();
        if ($roleName) {
            if (!$role = Role::where(['name' => $roleName])->first()) {
                $role = Role::factory()->create(['name' => $roleName]);
            }
            $user->roles()->attach($role);
            if ($account && !$accountUser) {
                AccountUser::factory()->create(
                    ['user_id' => $user->id, 'account_id' => $account->id, 'role' => $roleName]
                );
            }
        }

        $this->actingAs($user);

        return $this;
    }

    protected function signInAsSuperAdmin($user = null)
    {
        $user = $user ?: User::factory()->create();
        $user->fresh();
        $roleName = 'super-admin';
        if (!$role = Role::where(['name' => $roleName])->first()) {
            $role = Role::factory()->create(['name' => $roleName]);
        }
        $user->roles()->attach($role);

        $this->actingAs($user);

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableExceptionHandling();
    }

    // Hat tip, @adamwathan.
    protected function disableExceptionHandling()
    {
        $this->oldExceptionHandler = $this->app->make(ExceptionHandler::class);
        $this->app->instance(
            ExceptionHandler::class,
            new class extends Handler {
                public function __construct()
                {
                }

                public function report(\Throwable $e)
                {
                }

                public function render($request, \Throwable $e)
                {
                    throw $e;
                }
            }
        );
    }

    protected function withExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, $this->oldExceptionHandler);

        return $this;
    }

    protected function truncate($className, $safe = true)
    {
        if ($safe) {
            /** @var Collection $collection */
            $collection = call_user_func([$className, 'all']);
            $collection->each(
                function (Model $model) {
                    return $model->delete();
                }
            );

            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        call_user_func([$className, 'truncate']);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

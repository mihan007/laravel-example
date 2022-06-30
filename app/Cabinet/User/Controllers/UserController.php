<?php

namespace App\Cabinet\User\Controllers;

use App\Cabinet\User\Requests\CreateUserFormRequest;
use App\Domain\Account\Models\AccountUser;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use App\Support\Controllers\Controller;
use App\ViewModels\UserFormViewModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $userViewModel = new UserFormViewModel(current_account(), current_user());

        return view('pages.users', $userViewModel);
    }

    public function store(CreateUserFormRequest $request, $accountId)
    {
        $user = $this->create($request->all());
        $user->roles()->attach($request->role);
        AccountUser::create(
            [
                'account_id' => $accountId,
                'user_id' => $user->id,
                'role' => Role::find($request->role)->name,
            ]
        );

        return redirect()->back()->withMessage('Пользователь успешно добавлен.');
    }

    protected function create(array $data)
    {
        return User::create(
            [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'activated' => '1',
            ]
        );
    }

    public function edit($accountId, $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(
                [
                    'status' => 'error',
                    'data' => [
                            'message' => 'Пользователь не существует',
                        ],
                ]
            );
        }

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->getRole()->id ?? '',
                        'role_name' => $user->getRole()->name ?? '',
                    ],
                ],
            ]
        );
    }

    public function update(Request $request, $accountId, $id)
    {
        if ($request->role == User::ROLE_SUPER_ADMIN_ID) {
            if (! Auth::user()->hasRole(User::ROLE_SUPER_ADMIN_NAME)) {
                return redirect()->back()->withMessage('Недостаточно прав для добавления такого пользователя.');
            }
        }

        $user = User::find($request->id);
        $user_role = Role::find($request->role);
        if ($user_role->name != 'сustomers') {
            $user->name = $request->name;
        }

        $user->email = $request->email;

        if ($request->post('new_password')) {
            $user->password = Hash::make($request->post('new_password'));
        }

        $user->save();
        $user->detachRoles($user->roles);
        $user->roles()->attach($request->role);

        AccountUser::where('user_id', $user->id)
            ->where('account_id', $accountId)
            ->update(['role' => $user_role->name]);

        return redirect(route('account.users.index', ['accountId' => $accountId]))->withMessage('Пользователь успешно обновлен.');
    }

    public function destroy(Request $request)
    {
        if (! $request->has('id')) {
            redirect('users')->withMessage('Id пользователя не найден.');
        }

        $user = User::findOrFail($request->id);

        if (! $user) {
            redirect('users')->withMessage('Возникла ошибка при обновлении пользователя.');
        }
        $user->detachRoles($user->roles);
        AccountUser::where('user_id', $user->id)->delete();
        $user->delete();

        return 'Deleted';
    }

    public function admins()
    {
        abort(400, 'Not implemented');
        return UserResource::collection(User::getPossibleAccountAdmin());
    }
}

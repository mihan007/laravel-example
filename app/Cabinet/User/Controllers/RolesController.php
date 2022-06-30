<?php

namespace App\Cabinet\User\Controllers;

use App\Domain\User\Models\Role;
use App\Support\Controllers\Controller;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $roles = Role::all();

        return view('pages.roles', compact('roles'));
    }

    public function save(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'display_name' => 'required',
        ]);

        Role::create($request->all());

        return redirect()->back()->withMessage('Роль успешно добавлена');
    }

    public function edit($id)
    {
        $role = Role::find($id);

        if (! $role) {
            return response()->json([
                'status' => 'error',
                'data' => [
                        'message' => 'Роль не существует',
                    ],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $role,
        ]);
    }

    public function update(Request $request)
    {
        if (! $request->has('id')) {
            redirect('roles')->withMessage('Id роли не найден.');
        }

        $role = Role::find($request->id);

        if (! $role) {
            redirect('roles')->withMessage('Возникла ошибка при обновлении роли.');
        }

        $role->name = $request->name;
        $role->display_name = $request->display_name;
        $role->description = $request->description;

        $role->save();

        return redirect('roles')->withMessage('Роль успешно обновленна');
    }

    public function delete(Request $request)
    {
        if (! $request->has('id')) {
            redirect('roles')->withMessage('Id роли не найден.');
        }

        $role = Role::find($request->id);

        if (! $role) {
            redirect('roles')->withMessage('Возникла ошибка при удалении роли.');
        }

        $role->delete();

        return 'Deleted';
    }
}

<?php

namespace Database\Seeders;

use App\Domain\User\Models\Role;
use Illuminate\Database\Seeder;

class RenameAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::find(1);
        $role->display_name = 'Администратор аккаунта';
        $role->save();
    }
}

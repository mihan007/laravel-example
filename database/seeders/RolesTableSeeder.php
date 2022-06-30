<?php

namespace Database\Seeders;

use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role1 = new Role();
        $role1->name = 'admin';
        $role1->display_name = 'Администратор';
        $role1->save();

        $role2 = new Role();
        $role2->name = 'managers';
        $role2->display_name = 'Менеджеры';
        $role2->save();

        $role3 = new Role();
        $role3->name = 'сustomers';
        $role3->display_name = 'Клиенты';
        $role3->save();

        $user1 = User::where('email', '1@troiza.net')->first();
        $user1->roles()->attach($role1->id);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AddSuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->delete(4);

        DB::table('roles')->insert(
            [
                'id' => 4,
                'name' => 'super-admin',
                'display_name' => 'Суперадминистратор',
            ]
        );

        DB::table('role_user')
            ->where('user_id', 2)
            ->delete();

        DB::table('role_user')
            ->insert(['user_id' => 2, 'role_id' => 4]);
    }
}

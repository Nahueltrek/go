<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class CreateAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Administrador']);

        $user = User::firstOrCreate(
            ['email' => 'go@0km.app'],
            ['name' => 'Nahuel', 'password' => bcrypt('123momiaes')]
        );

        if (! $user->roles()->where('roles.id', $role->id)->exists()) {
            $user->roles()->attach($role);
        }

        echo "Admin creado: {$user->email}\n";
    }
}

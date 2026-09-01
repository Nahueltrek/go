<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'super_admin', 'label' => 'Super Administrador'],
            ['name' => 'admin', 'label' => 'Administrador de destino'],
            ['name' => 'editor', 'label' => 'Editor de contenido'],
            ['name' => 'business_owner', 'label' => 'Dueño de negocio'],
            ['name' => 'registered_user', 'label' => 'Usuario registrado'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                ['label' => $role['label'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $permissions = [
            'businesses.view', 'businesses.create', 'businesses.edit', 'businesses.delete',
            'businesses.edit_own', // solo su ficha reclamada
            'attractions.manage',
            'activities.manage',
            'routes.manage',
            'articles.manage', 'articles.publish',
            'events.manage',
            'claims.review',
            'verifications.review',
            'admin.access',
            'analytics.view',
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // super_admin recibe todos los permisos
        $superAdminId = DB::table('roles')->where('name', 'super_admin')->value('id');
        $allPermissionIds = DB::table('permissions')->pluck('id');

        foreach ($allPermissionIds as $permissionId) {
            DB::table('permission_role')->updateOrInsert([
                'role_id' => $superAdminId,
                'permission_id' => $permissionId,
            ]);
        }
    }
}

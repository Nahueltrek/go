<?php

namespace Tests\Feature\Admin;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre los puntos C, E y F del criterio de Sprint 0:
 * C. El flash 'success' llega a Inertia después de una acción admin.
 * E. Una acción admin correctamente autorizada funciona.
 * F. Una acción admin no autorizada es rechazada.
 */
class OrganizationActionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeUserWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['label' => ucfirst($roleName)]);

        $user = User::create([
            'name' => ucfirst($roleName).' Test',
            'email' => $roleName.'-test@example.test',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function makePendingOrganization(): Organization
    {
        return Organization::create([
            'type' => 'operador',
            'name' => 'Operador de Prueba',
            'slug' => 'operador-de-prueba-'.uniqid(),
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_approve_organization_and_flash_reaches_inertia(): void
    {
        $admin = $this->makeUserWithRole('admin');
        $organization = $this->makePendingOrganization();

        $this->actingAs($admin)
            ->post("/admin/organizaciones/{$organization->id}/approve")
            ->assertRedirect();

        $this->assertSame('approved', $organization->fresh()->status);

        // La sesión ya tiene el flash 'success' seteado por el redirect
        // anterior — un GET normal a cualquier página admin debe traerlo
        // via el prop 'flash' compartido en HandleInertiaRequests.
        $this->actingAs($admin)
            ->get('/admin/organizaciones')
            ->assertInertia(fn ($page) => $page
                ->where('flash.success', "{$organization->name} aprobado.")
            );
    }

    public function test_non_admin_cannot_approve_organization(): void
    {
        $user = $this->makeUserWithRole('registered_user');
        $organization = $this->makePendingOrganization();

        $this->actingAs($user)
            ->post("/admin/organizaciones/{$organization->id}/approve")
            ->assertForbidden();

        $this->assertSame('pending', $organization->fresh()->status);
    }
}

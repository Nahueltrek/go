<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre los puntos A, B y D del criterio de Sprint 0:
 * A. /admin usa GoChileDashboardController.
 * B. Las props 'stats' esperadas por Admin/Dashboard.vue existen.
 * D. Un usuario no autenticado no puede acceder a /admin.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Administrador']);

        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-test@example.test',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_admin_dashboard_is_wired_to_gochile_dashboard_controller(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('stats.organizaciones_pendientes')
            ->has('stats.organizaciones_aprobadas')
            ->has('stats.colaboraciones_pendientes')
            ->has('stats.experiencias_publicadas')
            ->has('stats.proyectos_publicados')
            ->has('stats.eventos_proximos')
            ->has('stats.articulos_publicados')
            // El controlador viejo (Admin\DashboardController) devolvía esta
            // clave en cambio — si algún día vuelve a estar mal wireado, este
            // assert lo detecta.
            ->missing('stats.businesses_active')
        );
    }
}

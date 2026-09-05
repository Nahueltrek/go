<?php

namespace Tests\Feature\Owner;

use App\Models\BusinessCategory;
use App\Models\Experience;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sprint 2 — Ownership. Cubre exactamente los casos pedidos en la
 * aprobación de Fase 2/3: guest bloqueado, owner no edita organización
 * ajena, owner no publica/aprueba/rechaza, owner crea Experience/Project
 * propios que nacen en draft, solo el owner envía su propio contenido a
 * revisión, solo admin publica/rechaza.
 */
class OwnershipTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => 'user-'.uniqid().'@example.test',
            'password' => bcrypt('password'),
        ]);
    }

    private function makeAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Administrador']);
        $user = $this->makeUser();
        $user->roles()->attach($role);

        return $user;
    }

    private function makeOrganization(?User $owner = null): Organization
    {
        return Organization::create([
            'user_id' => $owner?->id,
            'type' => 'operador',
            'name' => 'Organización de Prueba '.uniqid(),
            'slug' => 'org-'.uniqid(),
            'status' => 'approved',
        ]);
    }

    private function makeActivityType(): BusinessCategory
    {
        return BusinessCategory::create(['name' => 'Trekking '.uniqid(), 'slug' => 'trekking-'.uniqid()]);
    }

    // --- Acceso ---

    public function test_guest_cannot_access_owner_panel(): void
    {
        $this->get('/mi-organizacion')->assertRedirect('/login');
    }

    public function test_user_without_organization_gets_not_found_on_owner_panel(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/mi-organizacion')->assertNotFound();
    }

    // --- Organization ---

    public function test_owner_can_view_and_update_own_organization(): void
    {
        $owner = $this->makeUser();
        $org = $this->makeOrganization($owner);

        $this->actingAs($owner)->get('/mi-organizacion')->assertOk();

        $this->actingAs($owner)
            ->put('/mi-organizacion', ['description' => 'Descripción actualizada por el owner'])
            ->assertRedirect();

        $this->assertSame('Descripción actualizada por el owner', $org->fresh()->description);
    }

    public function test_owner_cannot_edit_another_organization_via_admin_route(): void
    {
        $owner = $this->makeUser();
        $this->makeOrganization($owner);

        $otherOrg = $this->makeOrganization($this->makeUser());

        $this->actingAs($owner)
            ->put("/admin/organizaciones/{$otherOrg->id}", ['description' => 'intento ajeno'])
            ->assertForbidden();

        $this->assertNotSame('intento ajeno', $otherOrg->fresh()->description);
    }

    // --- Experience ---

    public function test_owner_can_create_experience_and_it_starts_as_draft(): void
    {
        $owner = $this->makeUser();
        $this->makeOrganization($owner);
        $activityType = $this->makeActivityType();

        $this->actingAs($owner)->post('/mi-organizacion/experiencias', [
            'activity_type_id' => $activityType->id,
            'name' => 'Mi primera experiencia',
        ])->assertRedirect();

        $experience = Experience::where('name', 'Mi primera experiencia')->firstOrFail();
        $this->assertSame('draft', $experience->status);
    }

    public function test_owner_cannot_set_status_directly_on_create(): void
    {
        $owner = $this->makeUser();
        $this->makeOrganization($owner);
        $activityType = $this->makeActivityType();

        // 'status' => 'published' no es un campo aceptado por la validación
        // del controller — debe ser ignorado, no debe romper el request.
        $this->actingAs($owner)->post('/mi-organizacion/experiencias', [
            'activity_type_id' => $activityType->id,
            'name' => 'Intento de publicar directo',
            'status' => 'published',
        ])->assertRedirect();

        $experience = Experience::where('name', 'Intento de publicar directo')->firstOrFail();
        $this->assertSame('draft', $experience->status);
    }

    public function test_only_owner_can_submit_own_experience_for_review(): void
    {
        $owner = $this->makeUser();
        $org = $this->makeOrganization($owner);
        $activityType = $this->makeActivityType();

        $experience = Experience::create([
            'organization_id' => $org->id,
            'activity_type_id' => $activityType->id,
            'name' => 'Experiencia', 'slug' => 'experiencia-'.uniqid(), 'status' => 'draft',
        ]);

        $stranger = $this->makeUser();
        $this->actingAs($stranger)
            ->post("/mi-organizacion/experiencias/{$experience->id}/enviar-revision")
            ->assertForbidden();
        $this->assertSame('draft', $experience->fresh()->status);

        $this->actingAs($owner)
            ->post("/mi-organizacion/experiencias/{$experience->id}/enviar-revision")
            ->assertRedirect();
        $this->assertSame('pending_review', $experience->fresh()->status);
    }

    public function test_owner_cannot_approve_or_reject_experience(): void
    {
        $owner = $this->makeUser();
        $org = $this->makeOrganization($owner);
        $activityType = $this->makeActivityType();

        $experience = Experience::create([
            'organization_id' => $org->id,
            'activity_type_id' => $activityType->id,
            'name' => 'Experiencia', 'slug' => 'experiencia-'.uniqid(), 'status' => 'pending_review',
        ]);

        $this->actingAs($owner)
            ->post("/admin/experiencias/{$experience->id}/approve")
            ->assertForbidden();

        $this->actingAs($owner)
            ->post("/admin/experiencias/{$experience->id}/reject")
            ->assertForbidden();

        $this->assertSame('pending_review', $experience->fresh()->status);
    }

    public function test_admin_can_approve_and_reject_experience(): void
    {
        $admin = $this->makeAdmin();
        $org = $this->makeOrganization($this->makeUser());
        $activityType = $this->makeActivityType();

        $experience = Experience::create([
            'organization_id' => $org->id,
            'activity_type_id' => $activityType->id,
            'name' => 'Experiencia', 'slug' => 'experiencia-'.uniqid(), 'status' => 'pending_review',
        ]);

        $this->actingAs($admin)
            ->post("/admin/experiencias/{$experience->id}/approve")
            ->assertRedirect();
        $this->assertSame('published', $experience->fresh()->status);

        $experience->update(['status' => 'pending_review']);

        $this->actingAs($admin)
            ->post("/admin/experiencias/{$experience->id}/reject")
            ->assertRedirect();
        $this->assertSame('rejected', $experience->fresh()->status);
    }

    // --- Project (mismo patrón que Experience) ---

    public function test_owner_can_create_project_and_it_starts_as_draft(): void
    {
        $owner = $this->makeUser();
        $this->makeOrganization($owner);

        $this->actingAs($owner)->post('/mi-organizacion/proyectos', [
            'name' => 'Mi primer proyecto',
            'category' => 'conservacion',
        ])->assertRedirect();

        $project = Project::where('name', 'Mi primer proyecto')->firstOrFail();
        $this->assertSame('draft', $project->status);
    }

    public function test_only_owner_can_submit_own_project_for_review(): void
    {
        $owner = $this->makeUser();
        $org = $this->makeOrganization($owner);

        $project = Project::create([
            'organization_id' => $org->id,
            'name' => 'Proyecto', 'slug' => 'proyecto-'.uniqid(),
            'category' => 'conservacion', 'status' => 'draft',
        ]);

        $stranger = $this->makeUser();
        $this->actingAs($stranger)
            ->post("/mi-organizacion/proyectos/{$project->id}/enviar-revision")
            ->assertForbidden();

        $this->actingAs($owner)
            ->post("/mi-organizacion/proyectos/{$project->id}/enviar-revision")
            ->assertRedirect();
        $this->assertSame('pending_review', $project->fresh()->status);
    }

    public function test_owner_cannot_approve_or_reject_project(): void
    {
        $owner = $this->makeUser();
        $org = $this->makeOrganization($owner);

        $project = Project::create([
            'organization_id' => $org->id,
            'name' => 'Proyecto', 'slug' => 'proyecto-'.uniqid(),
            'category' => 'conservacion', 'status' => 'pending_review',
        ]);

        $this->actingAs($owner)
            ->post("/admin/proyectos/{$project->id}/approve")
            ->assertForbidden();

        $this->assertSame('pending_review', $project->fresh()->status);
    }

    public function test_admin_can_approve_and_reject_project(): void
    {
        $admin = $this->makeAdmin();
        $org = $this->makeOrganization($this->makeUser());

        $project = Project::create([
            'organization_id' => $org->id,
            'name' => 'Proyecto', 'slug' => 'proyecto-'.uniqid(),
            'category' => 'conservacion', 'status' => 'pending_review',
        ]);

        $this->actingAs($admin)
            ->post("/admin/proyectos/{$project->id}/approve")
            ->assertRedirect();
        $this->assertSame('published', $project->fresh()->status);
    }

    // --- CollaborationRequest → Organization.user_id ---

    public function test_collaboration_request_approve_sets_organization_owner_when_authenticated(): void
    {
        $requester = $this->makeUser();

        $this->actingAs($requester)->post('/colaboradores', [
            'type' => 'operador',
            'name' => 'Nueva Org Postulante',
        ])->assertRedirect();

        $request = \App\Models\CollaborationRequest::where('name', 'Nueva Org Postulante')->firstOrFail();
        $this->assertSame($requester->id, $request->user_id);

        $organization = $request->approve();

        $this->assertSame($requester->id, $organization->user_id);
        $this->assertTrue($organization->isOwnedBy($requester));
    }

    public function test_collaboration_request_stays_anonymous_when_not_authenticated(): void
    {
        $this->post('/colaboradores', [
            'type' => 'operador',
            'name' => 'Org Anónima',
        ])->assertRedirect();

        $request = \App\Models\CollaborationRequest::where('name', 'Org Anónima')->firstOrFail();
        $this->assertNull($request->user_id);

        $organization = $request->approve();
        $this->assertNull($organization->user_id);
    }

    // --- Fase 4: scoping (Owner ve únicamente lo suyo) ---

    public function test_owner_index_only_shows_own_experiences(): void
    {
        $owner = $this->makeUser();
        $org = $this->makeOrganization($owner);
        $activityType = $this->makeActivityType();

        Experience::create([
            'organization_id' => $org->id, 'activity_type_id' => $activityType->id,
            'name' => 'Propia', 'slug' => 'propia-'.uniqid(), 'status' => 'draft',
        ]);

        $otherOrg = $this->makeOrganization($this->makeUser());
        Experience::create([
            'organization_id' => $otherOrg->id, 'activity_type_id' => $activityType->id,
            'name' => 'Ajena', 'slug' => 'ajena-'.uniqid(), 'status' => 'draft',
        ]);

        $response = $this->actingAs($owner)->get('/mi-organizacion/experiencias');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('experiences', 1)
            ->where('experiences.0.name', 'Propia')
        );
    }

    public function test_owner_index_only_shows_own_projects(): void
    {
        $owner = $this->makeUser();
        $org = $this->makeOrganization($owner);

        Project::create([
            'organization_id' => $org->id, 'name' => 'Propio', 'slug' => 'propio-'.uniqid(),
            'category' => 'conservacion', 'status' => 'draft',
        ]);

        $otherOrg = $this->makeOrganization($this->makeUser());
        Project::create([
            'organization_id' => $otherOrg->id, 'name' => 'Ajeno', 'slug' => 'ajeno-'.uniqid(),
            'category' => 'conservacion', 'status' => 'draft',
        ]);

        $response = $this->actingAs($owner)->get('/mi-organizacion/proyectos');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('projects', 1)
            ->where('projects.0.name', 'Propio')
        );
    }

    // --- Fase 4: acceso a contenido ajeno directo (no solo vía submitForReview) ---

    public function test_owner_cannot_edit_experience_of_another_organization(): void
    {
        $owner = $this->makeUser();
        $this->makeOrganization($owner);

        $otherOrg = $this->makeOrganization($this->makeUser());
        $activityType = $this->makeActivityType();
        $experience = Experience::create([
            'organization_id' => $otherOrg->id, 'activity_type_id' => $activityType->id,
            'name' => 'Ajena', 'slug' => 'ajena-'.uniqid(), 'status' => 'draft',
        ]);

        $this->actingAs($owner)
            ->get("/mi-organizacion/experiencias/{$experience->id}/editar")
            ->assertForbidden();

        $this->actingAs($owner)
            ->put("/mi-organizacion/experiencias/{$experience->id}", ['name' => 'hackeado', 'activity_type_id' => $activityType->id])
            ->assertForbidden();
    }

    public function test_owner_cannot_edit_project_of_another_organization(): void
    {
        $owner = $this->makeUser();
        $this->makeOrganization($owner);

        $otherOrg = $this->makeOrganization($this->makeUser());
        $project = Project::create([
            'organization_id' => $otherOrg->id, 'name' => 'Ajeno', 'slug' => 'ajeno-'.uniqid(),
            'category' => 'conservacion', 'status' => 'draft',
        ]);

        $this->actingAs($owner)
            ->put("/mi-organizacion/proyectos/{$project->id}", ['name' => 'hackeado', 'category' => 'conservacion'])
            ->assertForbidden();
    }

    // --- Fase 4: no editar mientras está pending_review ---

    public function test_owner_cannot_update_experience_while_pending_review_but_can_view_it(): void
    {
        $owner = $this->makeUser();
        $org = $this->makeOrganization($owner);
        $activityType = $this->makeActivityType();

        $experience = Experience::create([
            'organization_id' => $org->id, 'activity_type_id' => $activityType->id,
            'name' => 'En revisión', 'slug' => 'en-revision-'.uniqid(), 'status' => 'pending_review',
        ]);

        $this->actingAs($owner)
            ->get("/mi-organizacion/experiencias/{$experience->id}/editar")
            ->assertOk();

        $this->actingAs($owner)
            ->put("/mi-organizacion/experiencias/{$experience->id}", ['name' => 'cambiado', 'activity_type_id' => $activityType->id])
            ->assertForbidden();

        $this->assertSame('En revisión', $experience->fresh()->name);
    }

    // --- Fase 4: campos mínimos antes de enviar a revisión ---

    public function test_owner_cannot_submit_experience_for_review_without_description(): void
    {
        $owner = $this->makeUser();
        $org = $this->makeOrganization($owner);
        $activityType = $this->makeActivityType();

        $experience = Experience::create([
            'organization_id' => $org->id, 'activity_type_id' => $activityType->id,
            'name' => 'Sin descripción', 'slug' => 'sin-desc-'.uniqid(), 'status' => 'draft',
            'description' => null,
        ]);

        $this->actingAs($owner)
            ->post("/mi-organizacion/experiencias/{$experience->id}/enviar-revision")
            ->assertRedirect();

        $this->assertSame('draft', $experience->fresh()->status);
    }

    // --- Fase 4: bandeja de moderación admin ---

    public function test_admin_sees_pending_content_in_moderation_inbox(): void
    {
        $admin = $this->makeAdmin();
        $org = $this->makeOrganization($this->makeUser());
        $activityType = $this->makeActivityType();

        Experience::create([
            'organization_id' => $org->id, 'activity_type_id' => $activityType->id,
            'name' => 'Pendiente', 'slug' => 'pendiente-'.uniqid(), 'status' => 'pending_review',
        ]);
        Project::create([
            'organization_id' => $org->id, 'name' => 'Pendiente P', 'slug' => 'pendiente-p-'.uniqid(),
            'category' => 'conservacion', 'status' => 'pending_review',
        ]);

        $response = $this->actingAs($admin)->get('/admin/moderacion');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('experiences', 1)
            ->has('projects', 1)
        );
    }

    public function test_admin_can_reject_project(): void
    {
        $admin = $this->makeAdmin();
        $org = $this->makeOrganization($this->makeUser());

        $project = Project::create([
            'organization_id' => $org->id, 'name' => 'Proyecto', 'slug' => 'proyecto-'.uniqid(),
            'category' => 'conservacion', 'status' => 'pending_review',
        ]);

        $this->actingAs($admin)
            ->post("/admin/proyectos/{$project->id}/reject")
            ->assertRedirect();

        $this->assertSame('rejected', $project->fresh()->status);
    }
}

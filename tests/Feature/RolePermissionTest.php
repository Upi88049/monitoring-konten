<?php

namespace Tests\Feature;

use App\Enums\ContentPillar;
use App\Enums\ContentType;
use App\Models\ContentLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $viewerUser;

    private User $editorUser;

    private User $adminKontenUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $superAdminRole = Role::where('name', 'super_admin')->firstOrFail();
        $viewerRole = Role::where('name', 'viewer')->firstOrFail();
        $editorRole = Role::where('name', 'editor_konten')->firstOrFail();
        $adminKontenRole = Role::where('name', 'admin_konten')->firstOrFail();

        $this->superAdmin = User::factory()->create(['role_id' => $superAdminRole->id]);
        $this->viewerUser = User::factory()->create(['role_id' => $viewerRole->id]);
        $this->editorUser = User::factory()->create(['role_id' => $editorRole->id]);
        $this->adminKontenUser = User::factory()->create(['role_id' => $adminKontenRole->id]);
    }

    public function test_super_admin_can_access_roles_and_users_management(): void
    {
        $this->actingAs($this->superAdmin);

        $responseRoles = $this->get(route('roles.index'));
        $responseRoles->assertStatus(200);
        $responseRoles->assertSee('Daftar Role Akun');
        $responseRoles->assertSee('Hak Akses Fitur');

        $responseUsers = $this->get(route('users.index'));
        $responseUsers->assertStatus(200);
        $responseUsers->assertSee('Daftar Akun Pengguna');
    }

    public function test_viewer_and_editor_cannot_access_role_management(): void
    {
        $this->actingAs($this->viewerUser);
        $this->get(route('roles.index'))->assertStatus(403);
        $this->get(route('users.index'))->assertStatus(403);

        $this->actingAs($this->editorUser);
        $this->get(route('roles.index'))->assertStatus(403);
        $this->get(route('users.index'))->assertStatus(403);
    }

    public function test_viewer_can_view_content_logs_but_cannot_add_edit_or_delete(): void
    {
        $log = ContentLog::factory()->create();

        $this->actingAs($this->viewerUser);

        // Can view table
        $this->get(route('content-logs.index'))
            ->assertStatus(200)
            ->assertSee('View Only');

        // Cannot add
        $this->post(route('content-logs.store'), [
            'post_date' => '2026-03-01',
            'content_type' => ContentType::Feed->value,
            'title' => 'Viewer Post Test',
            'pillar' => ContentPillar::EmployerBranding->value,
        ])->assertStatus(403);

        // Cannot edit
        $this->put(route('content-logs.update', $log), [
            'post_date' => '2026-03-01',
            'content_type' => ContentType::Feed->value,
            'title' => 'Edited by Viewer',
            'pillar' => ContentPillar::EmployerBranding->value,
        ])->assertStatus(403);

        // Cannot edit metrics
        $this->post(route('content-logs.metrics', $log), [
            'views_h30' => 500,
            'likes' => 10,
            'comments' => 2,
            'shares_reposts' => 1,
            'saves' => 3,
        ])->assertStatus(403);

        // Cannot delete
        $this->delete(route('content-logs.destroy', $log))->assertStatus(403);
    }

    public function test_viewer_cannot_import_excel(): void
    {
        $this->actingAs($this->viewerUser);

        $this->post(route('import.default'))->assertStatus(403);
    }

    public function test_viewer_can_export_csv(): void
    {
        $this->actingAs($this->viewerUser);

        $response = $this->get(route('export.csv'));
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type', ''), 'text/csv'));
    }

    public function test_editor_can_view_add_and_edit_content_but_cannot_delete(): void
    {
        $log = ContentLog::factory()->create();

        $this->actingAs($this->editorUser);

        // Can add
        $responseCreate = $this->post(route('content-logs.store'), [
            'post_date' => '2026-03-01',
            'content_type' => ContentType::Feed->value,
            'title' => 'Editor New Content',
            'pillar' => ContentPillar::EmployerBranding->value,
        ]);
        $responseCreate->assertRedirect(route('content-logs.index'));
        $this->assertDatabaseHas('content_logs', ['title' => 'Editor New Content']);

        // Can edit
        $responseEdit = $this->put(route('content-logs.update', $log), [
            'post_date' => '2026-03-01',
            'content_type' => ContentType::Feed->value,
            'title' => 'Updated by Editor',
            'pillar' => ContentPillar::EmployerBranding->value,
        ]);
        $responseEdit->assertRedirect(route('content-logs.index'));
        $this->assertDatabaseHas('content_logs', ['id' => $log->id, 'title' => 'Updated by Editor']);

        // Cannot delete
        $this->delete(route('content-logs.destroy', $log))->assertStatus(403);
        $this->assertDatabaseHas('content_logs', ['id' => $log->id]);
    }

    public function test_super_admin_can_create_custom_role_with_specific_permissions(): void
    {
        $this->actingAs($this->superAdmin);

        $viewPerm = Permission::where('name', 'content_logs.view')->firstOrFail();
        $createPerm = Permission::where('name', 'content_logs.create')->firstOrFail();

        $response = $this->post(route('roles.store'), [
            'name' => 'data_entry_intern',
            'label' => 'Data Entry Intern',
            'description' => 'Hanya bisa input konten baru dan lihat',
            'permissions' => [$viewPerm->id, $createPerm->id],
        ]);

        $response->assertRedirect(route('roles.index'));
        $this->assertDatabaseHas('roles', ['name' => 'data_entry_intern']);

        $role = Role::where('name', 'data_entry_intern')->first();
        $this->assertCount(2, $role->permissions);
        $this->assertTrue($role->hasPermission('content_logs.view'));
        $this->assertTrue($role->hasPermission('content_logs.create'));
        $this->assertFalse($role->hasPermission('content_logs.edit'));
        $this->assertFalse($role->hasPermission('content_logs.delete'));
    }

    public function test_super_admin_can_update_role_permissions_dynamically(): void
    {
        $this->actingAs($this->superAdmin);

        $role = Role::where('name', 'editor_konten')->firstOrFail();
        $viewPerm = Permission::where('name', 'content_logs.view')->firstOrFail();

        // Revoke create and edit, keep only view
        $response = $this->put(route('roles.update', $role), [
            'label' => 'Editor Konten (Restricted)',
            'permissions' => [$viewPerm->id],
        ]);

        $response->assertRedirect(route('roles.index'));

        // Now $editorUser tries to create content -> should be 403 Forbidden
        $this->actingAs($this->editorUser->fresh());
        $this->post(route('content-logs.store'), [
            'post_date' => '2026-03-01',
            'content_type' => ContentType::Feed->value,
            'title' => 'Should Be Forbidden',
            'pillar' => ContentPillar::EmployerBranding->value,
        ])->assertStatus(403);
    }

    public function test_super_admin_cannot_delete_system_role(): void
    {
        $this->actingAs($this->superAdmin);

        $systemRole = Role::where('name', 'super_admin')->firstOrFail();
        $response = $this->delete(route('roles.destroy', $systemRole));

        $response->assertRedirect(route('roles.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'super_admin']);
    }

    public function test_super_admin_can_create_and_update_users_with_roles(): void
    {
        $this->actingAs($this->superAdmin);

        $viewerRole = Role::where('name', 'viewer')->firstOrFail();
        $adminKontenRole = Role::where('name', 'admin_konten')->firstOrFail();

        // Create new user with viewer role
        $responseStore = $this->post(route('users.store'), [
            'name' => 'New Staff',
            'email' => 'staff@dharmagroup.co.id',
            'password' => 'password123',
            'role_id' => $viewerRole->id,
        ]);

        $responseStore->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'staff@dharmagroup.co.id', 'role_id' => $viewerRole->id]);

        $newUser = User::where('email', 'staff@dharmagroup.co.id')->firstOrFail();

        // Promote user to admin_konten
        $responseUpdate = $this->put(route('users.update', $newUser), [
            'name' => 'New Staff Promoted',
            'email' => 'staff@dharmagroup.co.id',
            'role_id' => $adminKontenRole->id,
        ]);

        $responseUpdate->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'staff@dharmagroup.co.id', 'role_id' => $adminKontenRole->id]);
    }

    public function test_super_admin_cannot_delete_own_account(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->delete(route('users.destroy', $this->superAdmin));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id]);
    }

    public function test_navbar_import_and_create_buttons_only_appear_on_content_logs_page(): void
    {
        $this->actingAs($this->superAdmin);

        // On content-logs page, buttons appear in navbar
        $responseContentLogs = $this->get(route('content-logs.index'));
        $responseContentLogs->assertStatus(200);
        $responseContentLogs->assertSee('+ Tambah Konten');
        $responseContentLogs->assertSee('Import Excel');

        // On dashboard page, + Tambah Konten is not in navbar
        $responseDashboard = $this->get(route('dashboard'));
        $responseDashboard->assertStatus(200);
        $responseDashboard->assertDontSee('+ Tambah Konten');

        // On roles page, + Tambah Konten is not in navbar
        $responseRoles = $this->get(route('roles.index'));
        $responseRoles->assertStatus(200);
        $responseRoles->assertDontSee('+ Tambah Konten');

        // On users page, + Tambah Konten is not in navbar
        $responseUsers = $this->get(route('users.index'));
        $responseUsers->assertStatus(200);
        $responseUsers->assertDontSee('+ Tambah Konten');
    }
}

<?php

namespace Tests\Feature;

use App\Enums\ContentPillar;
use App\Enums\ContentType;
use App\Models\ActivityLog;
use App\Models\ContentLog;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $adminKonten;

    private User $viewerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $superAdminRole = Role::where('name', 'super_admin')->firstOrFail();
        $adminKontenRole = Role::where('name', 'admin_konten')->firstOrFail();
        $viewerRole = Role::where('name', 'viewer')->firstOrFail();

        $this->superAdmin = User::factory()->create(['role_id' => $superAdminRole->id]);
        $this->adminKonten = User::factory()->create(['role_id' => $adminKontenRole->id]);
        $this->viewerUser = User::factory()->create(['role_id' => $viewerRole->id]);
    }

    public function test_super_admin_and_admin_konten_can_access_activity_logs(): void
    {
        $this->actingAs($this->superAdmin);
        $this->get(route('activity-logs.index'))->assertStatus(200)->assertSee('Audit Trail & Riwayat Aktivitas', false);

        $this->actingAs($this->adminKonten);
        $this->get(route('activity-logs.index'))->assertStatus(200)->assertSee('Audit Trail & Riwayat Aktivitas', false);
    }

    public function test_viewer_without_permission_cannot_access_activity_logs(): void
    {
        $this->actingAs($this->viewerUser);
        $this->get(route('activity-logs.index'))->assertStatus(403);
    }

    public function test_content_crud_operations_are_logged_automatically(): void
    {
        $this->actingAs($this->superAdmin);

        // 1. Create content
        $responseCreate = $this->post(route('content-logs.store'), [
            'post_date' => '2026-03-01',
            'content_type' => ContentType::Reels->value,
            'title' => 'Postingan Uji Coba Log',
            'pillar' => ContentPillar::EmployerBranding->value,
            'pic_name' => 'Budi Santoso',
        ]);
        $responseCreate->assertRedirect(route('content-logs.index'));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'content_create',
            'module' => 'Master Log Konten',
        ]);

        $content = ContentLog::where('title', 'Postingan Uji Coba Log')->firstOrFail();

        // 2. Update content
        $this->put(route('content-logs.update', $content), [
            'post_date' => '2026-03-01',
            'content_type' => ContentType::Reels->value,
            'title' => 'Postingan Uji Coba Log Diperbarui',
            'pillar' => ContentPillar::EmployerBranding->value,
            'pic_name' => 'Budi Santoso',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'content_update',
            'module' => 'Master Log Konten',
        ]);

        // 3. Update metrics H+30
        $this->post(route('content-logs.metrics', $content), [
            'views_h30' => 5000,
            'viewers_h30' => 4500,
            'likes' => 300,
            'comments' => 50,
            'shares_reposts' => 40,
            'saves' => 20,
            'follows' => 15,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'content_metrics_update',
            'module' => 'Master Log Konten',
        ]);

        // 4. Delete content
        $this->delete(route('content-logs.destroy', $content));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'content_delete',
            'module' => 'Master Log Konten',
        ]);
    }

    public function test_user_and_role_operations_are_logged(): void
    {
        $this->actingAs($this->superAdmin);

        // 1. Role create
        $this->post(route('roles.store'), [
            'name' => 'auditor_khusus',
            'label' => 'Auditor Khusus',
            'description' => 'Role untuk auditor eksternal',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'role_create',
            'module' => 'Manajemen Role',
        ]);

        // 2. User create
        $role = Role::where('name', 'auditor_khusus')->firstOrFail();
        $this->post(route('users.store'), [
            'name' => 'Auditor Satu',
            'email' => 'auditor1@example.com',
            'password' => 'Password123!',
            'role_id' => $role->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user_create',
            'module' => 'Manajemen Pengguna',
        ]);
    }

    public function test_activity_logs_filter_and_search(): void
    {
        $this->actingAs($this->superAdmin);

        ActivityLog::create([
            'user_id' => $this->superAdmin->id,
            'user_name' => $this->superAdmin->name,
            'action' => 'custom_search_action',
            'module' => 'Modul Spesifik',
            'description' => 'Aktivitas sangat unik untuk filter test',
            'ip_address' => '127.0.0.1',
        ]);

        $responseSearch = $this->get(route('activity-logs.index', ['search' => 'sangat unik']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Aktivitas sangat unik');

        $responseFilterModule = $this->get(route('activity-logs.index', ['module' => 'Modul Spesifik']));
        $responseFilterModule->assertStatus(200);
        $responseFilterModule->assertSee('Modul Spesifik');
    }
}

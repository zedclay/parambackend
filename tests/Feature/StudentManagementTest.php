<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        Sanctum::actingAs($admin);
    }

    public function test_admin_can_create_student(): void
    {
        $response = $this->postJson('/api/admin/students', [
            'name' => 'New Student',
            'email' => 'newstudent@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'email' => 'newstudent@test.com',
            'role' => 'student',
        ]);
    }

    public function test_admin_can_list_students(): void
    {
        User::factory()->count(5)->create(['role' => 'student']);

        $response = $this->getJson('/api/admin/students');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_student_cannot_access_admin_endpoints(): void
    {
        $student = User::create([
            'name' => 'Student',
            'email' => 'student@test.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        Sanctum::actingAs($student);

        $response = $this->getJson('/api/admin/students');

        $response->assertStatus(403);
    }
}

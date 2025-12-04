<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotesManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        Sanctum::actingAs($admin);
    }

    public function test_admin_can_upload_note(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->create('test.pdf', 100);

        $response = $this->postJson('/api/admin/notes', [
            'title' => 'Test Note',
            'description' => 'Test Description',
            'file' => $file,
            'visibility' => 'private',
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);
    }

    public function test_admin_can_list_notes(): void
    {
        $response = $this->getJson('/api/admin/notes');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}

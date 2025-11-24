<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser()
    {
        return User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_profile_information_can_be_updated()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->patch('/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_profile_information_update_preserves_existing_data()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        // Update only name, email should remain unchanged
        $response = $this->patch('/profile', [
            'name' => 'Updated Name Only',
            // Not sending email, should not be set to null
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('Updated Name Only', $user->name);
        $this->assertSame('test@example.com', $user->email); // Should remain unchanged
    }

    public function test_profile_can_be_updated_with_photo()
    {
        Storage::fake('public');
        $user = $this->createUser();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('profile.jpg');

        $response = $this->post('/user/profil', [
            'name' => 'Photo User',
            'email' => 'photo@example.com',
            'profile_pict' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $user->refresh();
        $this->assertSame('Photo User', $user->name);
        $this->assertSame('photo@example.com', $user->email);
        $this->assertNotNull($user->profile_pict);
        Storage::disk('public')->assertExists($user->profile_pict);
    }

    public function test_profile_update_with_photo_preserves_existing_data()
    {
        Storage::fake('public');
        $user = $this->createUser();
        $this->actingAs($user);

        // First update with a photo
        $file = UploadedFile::fake()->image('profile.jpg');
        $response = $this->post('/user/profil', [
            'name' => 'Photo User',
            'email' => 'photo@example.com',
            'profile_pict' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();

        $originalProfilePic = $user->profile_pict;
        $this->assertNotNull($originalProfilePic);

        // Now update without sending name/email, should preserve existing values
        $response = $this->post('/user/profil', [
            'jabatan' => 'New Position',
            // Not sending name or email, should preserve existing values
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();

        $this->assertSame('Photo User', $user->name); // Should remain unchanged
        $this->assertSame('photo@example.com', $user->email); // Should remain unchanged
        $this->assertSame($originalProfilePic, $user->profile_pict); // Should remain unchanged
        $this->assertSame('New Position', $user->jabatan);
    }
}
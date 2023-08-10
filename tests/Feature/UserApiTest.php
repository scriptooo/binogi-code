<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function testCreateUserWithValidNickname()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'test_name',
            'email' => 'test@example.com',
            'nickname' => 'test_nick_name',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'nickname' => 'test_nick_name',
        ]);
    }

    public function testCreateUserWithDuplicateNickname()
    {
        $user = User::factory()->create(['nickname' => 'johndoe']);

        $response = $this->postJson('/api/users', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'nickname' => 'johndoe',
        ]);

        $response->assertStatus(422); 
    }

    public function testCreateUserWithTooLongNickname()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'Alice Brown',
            'email' => 'alice@example.com',
            'nickname' => 'averylongnicknamethatexceedsthirtycharacters',
        ]);

        $response->assertStatus(422);
    }

    public function testGetUserWithNickname()
    {
        $user = factory(User::class)->create(['nickname' => 'test_nickname']);

        $response = $this->get('/api/users/' . $user->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'nickname' => 'test_nickname',
            ]);
    }

    public function testUpdateUserNickname()
    {
        $user = factory(User::class)->create();

        $response = $this->put('/api/users/' . $user->id, [
            'nickname' => 'new_nickname',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nickname' => 'new_nickname',
        ]);
    }

    public function testInvalidNicknameFails()
    {
        $response = $this->post('/api/users', [
            'nickname' => str_repeat('a', 31), // Invalid nickname length
        ]);

        $response->assertStatus(422); // Expecting validation failure
    }


}

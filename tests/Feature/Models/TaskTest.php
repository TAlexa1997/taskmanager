<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{

    use RefreshDatabase; 
    public function test_can_get_all_tasks(): void
    {
        $user = User::factory()->create();
        Task::create([
            'user_id' => $user->id,
            'title' => 'Teszt feladat listázáshoz',
            'status' => 'pending'
        ]);

        $response = $this->getJson('/api/tasks');
        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    public function test_can_create_a_task(): void
    {
        $user = User::factory()->create();

        $taskData = [
            'user_id' => $user->id,
            'title' => 'Új teszt feladat',
            'description' => 'Ezt az automatizált teszt hozta létre.',
            'status' => 'in_progress'
        ];

        $response = $this->postJson('/api/tasks', $taskData);
        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Új teszt feladat']);
        
        $this->assertDatabaseHas('tasks', [
            'title' => 'Új teszt feladat'
        ]);
    }
}
<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            // Seed published posts
            Post::factory()->count(rand(2, 4))->published()->create([
                'user_id' => $user->id,
            ]);

            // Seed draft posts
            Post::factory()->count(rand(0, 1))->create([
                'user_id' => $user->id,
            ]);
        }
    }
}

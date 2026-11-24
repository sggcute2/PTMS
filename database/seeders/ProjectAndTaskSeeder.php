<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;

class ProjectAndTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = User::first();
        $user2 = User::skip(1)->first();

        // Seed data Proyek untuk User 1
        $project1 = Project::create([
            'user_id' => $user1->id,
            'name' => 'Redesain Portal E-Commerce PT ABC',
            'description' => 'Memperbarui tampilan antarmuka dan meningkatkan performa checkout portal utama.',
        ]);

        Task::create([
            'project_id' => $project1->id,
            'title' => 'Buat Figma Design System',
            'status' => 'completed',
            'due_date' => now()->subDays(2),
        ]);

        Task::create([
            'project_id' => $project1->id,
            'title' => 'Slicing Halaman Produk ke TailwindCSS v4',
            'status' => 'in_progress',
            'due_date' => now()->addDays(3),
        ]);

        $project2 = Project::create([
            'user_id' => $user1->id,
            'name' => 'Pengembangan Mobile App API',
            'description' => 'Menyediakan endpoint REST API untuk autentikasi dan sinkronisasi data.',
        ]);

        Task::create([
            'project_id' => $project2->id,
            'title' => 'Dokumentasi OpenAPI / Swagger',
            'status' => 'pending',
            'due_date' => now()->addDays(14),
        ]);

        // Seed data Proyek untuk User 2
        $projectUser2 = Project::create([
            'user_id' => $user2->id,
            'name' => 'Proyek Private User 02',
            'description' => 'Hanya boleh dilihat dan dikelola oleh User 02. User 01 dilarang melihat.',
        ]);

        Task::create([
            'project_id' => $projectUser2->id,
            'title' => 'Audit Keamanan Internal',
            'status' => 'pending',
            'due_date' => now()->addDays(7),
        ]);
    }
}
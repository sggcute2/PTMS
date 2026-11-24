<?php

namespace Tests\Concurrency;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LockForUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_lock_for_update_executes_within_transaction(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Lock Project']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Pessimistic Task', 'status' => 'pending']);

        $lockedExecuted = false;

        DB::transaction(function () use ($task, &$lockedExecuted) {
            // Memastikan query lockForUpdate dapat dieksekusi dengan aman di dalam DB transaction
            $locked = Task::where('id', $task->id)->lockForUpdate()->first();

            $this->assertNotNull($locked);
            $this->assertEquals('pending', $locked->status);

            $locked->status = 'in_progress';
            $locked->save();

            $lockedExecuted = true;
        });

        $this->assertTrue($lockedExecuted);
        $this->assertEquals('in_progress', $task->fresh()->status);
    }

    public function test_pessimistic_lock_ensures_sequential_state_consistency(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Sequential Lock Project']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Sequence Task', 'status' => 'pending']);

        // Transaksi 1
        DB::transaction(function () use ($task) {
            $record = Task::where('id', $task->id)->lockForUpdate()->first();
            $record->status = 'in_progress';
            $record->save();
        });

        // Transaksi 2 segera setelahnya
        DB::transaction(function () use ($task) {
            $record = Task::where('id', $task->id)->lockForUpdate()->first();
            $this->assertEquals('in_progress', $record->status, 'Transaksi kedua harus membaca committed data dari transaksi pertama');
            $record->status = 'completed';
            $record->save();
        });

        $this->assertEquals('completed', $task->fresh()->status);
    }
}

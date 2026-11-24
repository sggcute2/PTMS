<?php

namespace App\Actions;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;

class UpdateTaskAction
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    public function execute(User $user, Task $task, array $data): Task
    {
        return $this->taskService->updateTask($user, $task, $data);
    }
}

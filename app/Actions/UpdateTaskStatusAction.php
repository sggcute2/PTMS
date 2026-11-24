<?php

namespace App\Actions;

use App\Models\Task;
use App\Services\TaskService;

class UpdateTaskStatusAction
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    public function execute(Task $task, string $status): Task
    {
        return $this->taskService->updateStatus($task, $status);
    }
}

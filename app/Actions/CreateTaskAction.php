<?php

namespace App\Actions;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;

class CreateTaskAction
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    public function execute(User $user, array $data): Task
    {
        return $this->taskService->createTask($user, $data);
    }
}

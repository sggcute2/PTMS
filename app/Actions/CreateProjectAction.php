<?php

namespace App\Actions;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;

class CreateProjectAction
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    public function execute(User $user, array $data): Project
    {
        return $this->projectService->createProject($user, $data);
    }
}

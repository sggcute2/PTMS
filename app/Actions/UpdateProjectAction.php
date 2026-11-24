<?php

namespace App\Actions;

use App\Models\Project;
use App\Services\ProjectService;

class UpdateProjectAction
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    public function execute(Project $project, array $data): Project
    {
        return $this->projectService->updateProject($project, $data);
    }
}

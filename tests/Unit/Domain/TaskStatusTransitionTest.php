<?php

namespace Tests\Unit\Domain;

use App\Domain\TaskStatusTransition;
use PHPUnit\Framework\TestCase;

class TaskStatusTransitionTest extends TestCase
{
    public function test_pending_can_only_transition_to_in_progress(): void
    {
        $this->assertTrue(TaskStatusTransition::canTransition(
            TaskStatusTransition::PENDING,
            TaskStatusTransition::IN_PROGRESS
        ));

        $this->assertFalse(TaskStatusTransition::canTransition(
            TaskStatusTransition::PENDING,
            TaskStatusTransition::COMPLETED
        ));

        $this->assertFalse(TaskStatusTransition::canTransition(
            TaskStatusTransition::PENDING,
            TaskStatusTransition::PENDING
        ));
    }

    public function test_in_progress_can_only_transition_to_completed(): void
    {
        $this->assertTrue(TaskStatusTransition::canTransition(
            TaskStatusTransition::IN_PROGRESS,
            TaskStatusTransition::COMPLETED
        ));

        $this->assertFalse(TaskStatusTransition::canTransition(
            TaskStatusTransition::IN_PROGRESS,
            TaskStatusTransition::PENDING
        ));
    }

    public function test_completed_cannot_transition_to_any_status(): void
    {
        $this->assertFalse(TaskStatusTransition::canTransition(
            TaskStatusTransition::COMPLETED,
            TaskStatusTransition::PENDING
        ));

        $this->assertFalse(TaskStatusTransition::canTransition(
            TaskStatusTransition::COMPLETED,
            TaskStatusTransition::IN_PROGRESS
        ));

        $this->assertTrue(TaskStatusTransition::isFinal(TaskStatusTransition::COMPLETED));
        $this->assertFalse(TaskStatusTransition::isFinal(TaskStatusTransition::PENDING));
        $this->assertFalse(TaskStatusTransition::isFinal(TaskStatusTransition::IN_PROGRESS));
    }

    public function test_next_status_returns_correct_step(): void
    {
        $this->assertEquals(TaskStatusTransition::IN_PROGRESS, TaskStatusTransition::nextStatus(TaskStatusTransition::PENDING));
        $this->assertEquals(TaskStatusTransition::COMPLETED, TaskStatusTransition::nextStatus(TaskStatusTransition::IN_PROGRESS));
        $this->assertNull(TaskStatusTransition::nextStatus(TaskStatusTransition::COMPLETED));
    }
}

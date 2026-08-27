<?php

namespace App\Repositories;

use App\Interfaces\TaskCommentRepositoryInterface;
use App\Models\TaskComment;
use Illuminate\Database\Eloquent\Collection;

class TaskCommentRepository implements TaskCommentRepositoryInterface
{
    public function getByTaskId(int $taskId): Collection
    {
        return TaskComment::with('user')
            ->where('project_task_id', $taskId)
            ->orderBy('created_at')
            ->get();
    }

    public function create(array $data): TaskComment
    {
        $comment = TaskComment::create($data);

        return $comment->load('user');
    }

    public function getById(string $id): TaskComment
    {
        return TaskComment::with('user')->findOrFail($id);
    }

    public function delete(string $id): TaskComment
    {
        $comment = TaskComment::findOrFail($id);
        $comment->delete();

        return $comment;
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\TaskCommentStoreRequest;
use App\Http\Resources\TaskCommentResource;
use App\Interfaces\TaskCommentRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class TaskCommentController extends Controller implements HasMiddleware
{
    private TaskCommentRepositoryInterface $taskCommentRepository;

    public function __construct(TaskCommentRepositoryInterface $taskCommentRepository)
    {
        $this->taskCommentRepository = $taskCommentRepository;
    }

    public static function middleware()
    {
        return [
            new Middleware(PermissionMiddleware::using(['task-list|task-create|task-edit|task-delete']), only: ['index']),
            new Middleware(PermissionMiddleware::using(['task-create']), only: ['store']),
        ];
    }

    /**
     * Daftar komentar pada satu task, terlama ke terbaru.
     */
    public function index(int $taskId)
    {
        try {
            $comments = $this->taskCommentRepository->getByTaskId($taskId);

            return ResponseHelper::jsonResponse(true, 'Task Comments Retrieved Successfully', TaskCommentResource::collection($comments), 200);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, 'Internal Server Error: '.$e->getMessage(), null, 500);
        }
    }

    public function store(TaskCommentStoreRequest $request, int $taskId)
    {
        $data = $request->validated();

        try {
            $comment = $this->taskCommentRepository->create([
                'project_task_id' => $taskId,
                'user_id' => auth()->id(),
                'body' => $data['body'],
            ]);

            return ResponseHelper::jsonResponse(true, 'Comment Added Successfully', new TaskCommentResource($comment), 201);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, 'Internal Server Error: '.$e->getMessage(), null, 500);
        }
    }

    /**
     * Hanya penulis komentar yang boleh menghapus komentarnya sendiri.
     */
    public function destroy(string $id)
    {
        try {
            $comment = $this->taskCommentRepository->getById($id);

            if ($comment->user_id !== auth()->id()) {
                return ResponseHelper::jsonResponse(false, 'Forbidden', null, 403);
            }

            $this->taskCommentRepository->delete($id);

            return ResponseHelper::jsonResponse(true, 'Comment Deleted Successfully', null, 200);
        } catch (ModelNotFoundException $e) {
            return ResponseHelper::jsonResponse(false, 'Comment Not Found', null, 404);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, 'Internal Server Error: '.$e->getMessage(), null, 500);
        }
    }
}

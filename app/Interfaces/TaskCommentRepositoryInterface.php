<?php

namespace App\Interfaces;

interface TaskCommentRepositoryInterface
{
    public function getByTaskId(
        int $taskId
    );

    public function create(
        array $data
    );

    public function getById(
        string $id
    );

    public function delete(
        string $id
    );
}

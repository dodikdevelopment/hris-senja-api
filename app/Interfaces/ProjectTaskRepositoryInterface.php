<?php

namespace App\Interfaces;

interface ProjectTaskRepositoryInterface
{
    public function getAll(
        ?string $search,
        ?int $projectId,
        ?int $limit,
        bool $execute,
        ?int $assigneeId = null,
        ?string $orderBy = null
    );

    public function getAllPaginated(
        ?string $search,
        ?int $projectId,
        int $rowPerPage,
        ?int $assigneeId = null
    );

    public function getById(
        string $id
    );

    public function create(
        array $data
    );

    public function update(
        string $id,
        array $data
    );

    public function delete(
        string $id
    );

    public function getByProjectId(
        int $projectId
    );
}

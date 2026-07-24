<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\DTOs\RequestDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface EloquentServiceInterface{
    /**
     * @return Collection<int, Model>
     */

    public function get(RequestDTO $request): Collection;

    public function paginate(
        RequestDTO $request,
    ): LengthAwarePaginator;

    public function find(
        RequestDTO $request,
        string $id,
    ): ?Model;

    public function firstWhere(
        RequestDTO $request,
        string $field,
        mixed $value,
    ): ?Model;

    public function exists(
        RequestDTO $request,
        string $field,
        mixed $value,
    ): bool;

    public function create(
        RequestDTO $request,
    ): Model;

    public function update(
        RequestDTO $request,
        string $id,
    ): ?Model;

    public function delete(
        RequestDTO $request,
        string $id,
    ): bool;
}
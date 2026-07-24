<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\RequestDTO;
use App\Services\Contracts\EloquentServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class EloquentService implements EloquentServiceInterface
{
    private function query(RequestDTO $request): Builder
    {
        $model = $request->requireModel();

        $query = $model->newQuery();

        $this->applyIncludes(
            query: $query,
            includes: $request->includes,
        );

        $this->applyFilters(
            query: $query,
            filters: $request->filters,
        );

        $this->applySort(
            query: $query,
            sort: $request->sort,
        );

        return $query;
    }

    private function applyIncludes(
        Builder $query,
        array $includes,
    ): void {
        foreach ($includes as $relation) {
            if (!is_string($relation) || $relation === '') {
                continue;
            }

            $query->with($relation);
        }
    }

    private function applyFilters(
        Builder $query,
        array $filters,
    ): void {
        foreach ($filters as $field => $value) {
            if (!is_string($field) || $field === '') {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }
            $query->where($field, $value);
        }
    }

    private function applySort(
        Builder $query,
        array $sort,
    ): void {
        foreach ($sort as $field => $direction) {
            if (!is_string($field) || $field === '') {
                continue;
            }

            $direction = strtolower((string) $direction);

            if (!in_array($direction, ['asc', 'desc'], true)) {
                throw new InvalidArgumentException(
                    "Invalid sort direction for '{$field}'."
                );
            }

            $query->orderBy($field, $direction);
        }
    }

    public function get(RequestDTO $request): Collection
    {
        return $this->query($request)->get();
    }

    public function paginate(
        RequestDTO $request,
    ): LengthAwarePaginator {
        $page = $request->page ?? 1;
        $pageSize = $request->pageSize ?? 15;

        return $this
            ->query($request)
            ->paginate(
                perPage: $pageSize,
                page: $page,
            );
    }

    public function find(
        RequestDTO $request,
        string $id,
    ): ?Model {
        return $this
            ->query($request)
            ->find($id);
    }

    public function firstWhere(
        RequestDTO $request,
        string $field,
        mixed $value,
    ): ?Model {
        return $this
            ->query($request)
            ->where($field, $value)
            ->first();
    }

    public function exists(
        RequestDTO $request,
        string $field,
        mixed $value,
    ): bool {
        return $this
            ->query($request)
            ->where($field, $value)
            ->exists();
    }

    public function create(
        RequestDTO $request,
    ): Model {
        $model = $request->requireModel();

        return $model
            ->newQuery()
            ->create($request->data);
    }

    public function update(
        RequestDTO $request,
        string $id,
    ): ?Model {
        $model = $this->find(
            request: $request,
            id: $id,
        );

        if ($model === null) {
            return null;
        }

        $model->fill($request->data);
        $model->save();

        return $model->refresh();
    }

    public function delete(
        RequestDTO $request,
        string $id,
    ): bool {
        $model = $this->find(
            request: $request,
            id: $id,
        );

        if ($model === null) {
            return false;
        }

        return (bool) $model->delete();
    }
}
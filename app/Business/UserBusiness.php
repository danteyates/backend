<?php

declare(strict_types=1);

namespace App\Business;

use App\Business\Contracts\UserBusinessInterface;
use App\DTOs\RequestDTO;
use App\DTOs\ResponseDTO;
use App\Models\User;
use App\Services\Contracts\EloquentServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

final class UserBusiness implements UserBusinessInterface
{
    private const ALLOWED_FILTERS = [
        'name',
        'email',
        'role',
        'is_active',
    ];

    private const ALLOWED_SORTS = [
        'name',
        'email',
        'created_at',
        'updated_at',
    ];

    private const ALLOWED_INCLUDES = [];

    public function __construct(
        private readonly EloquentServiceInterface $eloquentService,
    ) {}

    public function get(RequestDTO $request): ResponseDTO
    {
        try {
            $request = $this->prepareRequest($request);
            $request = $this->sanitizeQueryOptions($request);

            $users = $request->hasPagination()
                ? $this->eloquentService->paginate($request)
                : $this->eloquentService->get($request);

            return ResponseDTO::success(
                data: $users,
                message: 'Users retrieved successfully.',
            );
        } catch (Throwable $exception) {
            return $this->serverError(
                exception: $exception,
                message: 'Users could not be retrieved.',
            );
        }
    }

    public function find(
        RequestDTO $request,
        string $id,
    ): ResponseDTO {
        try {
            $request = $this->prepareRequest($request);

            $user = $this->eloquentService->find(
                request: $request,
                id: $id,
            );

            if ($user === null) {
                return ResponseDTO::error(
                    message: 'User not found.',
                    statusCode: 404,
                );
            }

            return ResponseDTO::success(
                data: $user,
                message: 'User retrieved successfully.',
            );
        } catch (Throwable $exception) {
            return $this->serverError(
                exception: $exception,
                message: 'User could not be retrieved.',
            );
        }
    }

    public function create(
        RequestDTO $request,
    ): ResponseDTO {
        try {
            $request = $this->prepareRequest($request);

            $email = $request->data['email'] ?? null;

            if (!is_string($email) || $email === '') {
                return ResponseDTO::error(
                    message: 'Email is required.',
                    errors: [
                        'email' => [
                            'Email is required.',
                        ],
                    ],
                    statusCode: 422,
                );
            }

            $emailExists = $this->eloquentService->exists(
                request: $request,
                field: 'email',
                value: $email,
            );

            if ($emailExists) {
                return ResponseDTO::error(
                    message: 'Email is already in use.',
                    errors: [
                        'email' => [
                            'Email is already in use.',
                        ],
                    ],
                    statusCode: 422,
                );
            }

            $data = $request->data;

            if (isset($data['password'])) {
                $data['password'] = Hash::make(
                    (string) $data['password']
                );
            }

            $request = $request->withData($data);

            $user = $this->eloquentService->create($request);

            return ResponseDTO::success(
                data: $user,
                message: 'User created successfully.',
                statusCode: 201,
            );
        } catch (Throwable $exception) {
            return $this->serverError(
                exception: $exception,
                message: 'User could not be created.',
            );
        }
    }

    public function update(
        RequestDTO $request,
        string $id,
    ): ResponseDTO {
        try {
            $request = $this->prepareRequest($request);

            $existingUser = $this->eloquentService->find(
                request: $request,
                id: $id,
            );

            if ($existingUser === null) {
                return ResponseDTO::error(
                    message: 'User not found.',
                    statusCode: 404,
                );
            }

            $data = $request->data;

            if (isset($data['email'])) {
                $emailUser = $this->eloquentService->firstWhere(
                    request: $request,
                    field: 'email',
                    value: $data['email'],
                );

                if ($emailUser !== null && (string) $emailUser->getKey() !== $id) {
                    return ResponseDTO::error(
                        message: 'Email is already in use.',
                        errors: [
                            'email' => [
                                'Email is already in use.',
                            ],
                        ],
                        statusCode: 422,
                    );
                }
            }

            if (array_key_exists('password', $data) && $data['password'] !== null && $data['password'] !== '') {
                $data['password'] = Hash::make(
                    (string) $data['password']
                );
            } else {
                unset($data['password']);
            }

            $request = $request->withData($data);

            $user = $this->eloquentService->update(
                request: $request,
                id: $id,
            );

            if ($user === null) {
                return ResponseDTO::error(
                    message: 'User not found.',
                    statusCode: 404,
                );
            }

            return ResponseDTO::success(
                data: $user,
                message: 'User updated successfully.',
            );
        } catch (Throwable $exception) {
            return $this->serverError(
                exception: $exception,
                message: 'User could not be updated.',
            );
        }
    }

    public function delete(
        RequestDTO $request,
        string $id,
    ): ResponseDTO {
        try {
            $request = $this->prepareRequest($request);

            $deleted = $this->eloquentService->delete(
                request: $request,
                id: $id,
            );

            if (! $deleted) {
                return ResponseDTO::error(
                    message: 'User not found.',
                    statusCode: 404,
                );
            }

            return ResponseDTO::success(
                data: true,
                message: 'User deleted successfully.',
            );
        } catch (Throwable $exception) {
            return $this->serverError(
                exception: $exception,
                message: 'User could not be deleted.',
            );
        }
    }

    private function prepareRequest(RequestDTO $request): RequestDTO
    {
        return $request->withModel(new User);
    }

    private function sanitizeQueryOptions(RequestDTO $request): RequestDTO
    {
        $filters = array_intersect_key(
            $request->filters,
            array_flip(self::ALLOWED_FILTERS),
        );

        $sort = array_intersect_key(
            $request->sort,
            array_flip(self::ALLOWED_SORTS),
        );

        $includes = array_values(
            array_intersect(
                $request->includes,
                self::ALLOWED_INCLUDES,
            )
        );

        return $request->withQueryOptions(
            filters: $filters,
            sort: $sort,
            includes: $includes,
        );
    }

    private function serverError(
        Throwable $exception,
        string $message,
    ): ResponseDTO {
        Log::error($message, [
            'exception' => $exception,
        ]);

        return ResponseDTO::error(
            message: $message,
            statusCode: 500,
        );
    }
}

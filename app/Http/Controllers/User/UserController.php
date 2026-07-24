<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Business\Contracts\UserBusinessInterface;
use App\DTOs\RequestDTO;
use App\DTOs\ResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserController extends Controller
{
    public function __construct(
        private readonly UserBusinessInterface $business,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $requestDTO = new RequestDTO(
            model: new User(),
            filters: $request->input('filters', []),
            sort: $request->input('sort', []),
            includes: $request->input('includes', []),
            page: $request->filled('page')
                ? $request->integer('page')
                : null,
            pageSize: $request->filled('pageSize')
                ? $request->integer('pageSize')
                : null,
            meta: $this->createMeta($request),
        );

        $responseDTO = $this->business->get($requestDTO);

        return $this->json($responseDTO);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $requestDTO = new RequestDTO(
            model: new User(),
            meta: $this->createMeta($request),
        );

        $responseDTO = $this->business->find(
            request: $requestDTO,
            id: $id,
        );

        return $this->json($responseDTO);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $requestDTO = new RequestDTO(
            model: new User(),
            data: $request->validated(),
            meta: $this->createMeta($request),
        );

        $responseDTO = $this->business->create(
            $requestDTO
        );

        return $this->json($responseDTO);
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $requestDTO = new RequestDTO(
            model: new User(),
            data: $request->validated(),
            meta: $this->createMeta($request),
        );

        $responseDTO = $this->business->update(
            request: $requestDTO,
            id: $id,
        );

        return $this->json($responseDTO);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $requestDTO = new RequestDTO(
            model: new User(),
            meta: $this->createMeta($request),
        );

        $responseDTO = $this->business->delete(
            request: $requestDTO,
            id: $id,
        );

        return $this->json($responseDTO);
    }

    private function json(ResponseDTO $response): JsonResponse
    {
        return response()->json(
            data: $response,
            status: $response->statusCode,
        );
    }

    private function createMeta(Request $request): array
    {
        return [
            'ip' => $request->ip(),
            'userAgent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
        ];
    }
}

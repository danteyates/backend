<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Business\Contracts\AuthBusinessInterface;
use App\DTOs\RequestDTO;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthBusinessInterface $authBusiness,
    ) {
    }

    public function register(Request $request): JsonResponse
    {
        $dto = new RequestDTO(
            model: new User(),
            data: $request->all(),
        );

        $response = $this->authBusiness->register($dto);

        return response()->json(
            data: $response,
            status: $response->statusCode,
        );
    }

    public function login(Request $request): JsonResponse
    {
        $dto = new RequestDTO(
            model: new User(),
            data: $request->all(),
        );

        $response = $this->authBusiness->login($dto);

        return response()->json(
            data: $response,
            status: $response->statusCode,
        );
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $response = $this->authBusiness->me($user);

        return response()->json(
            data: $response,
            status: $response->statusCode,
        );
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $response = $this->authBusiness->logout($user);

        return response()->json(
            data: $response,
            status: $response->statusCode,
        );
    }
}
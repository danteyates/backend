<?php

declare(strict_types=1);

namespace App\Business;

use App\Business\Contracts\AuthBusinessInterface;
use App\DTOs\RequestDTO;
use App\DTOs\ResponseDTO;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Services\Contracts\EloquentServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthBusiness implements AuthBusinessInterface
{
    public function __construct(
        private readonly EloquentServiceInterface $eloquentService,
    ) {
    }

    public function register(RequestDTO $request): ResponseDTO
    {
        try {
            $data = $request->data;

            $email = mb_strtolower(
                trim((string) $data['email'])
            );

            $userRequest = $request->withModel(
                new User()
            );

            $emailExists = $this->eloquentService->exists(
                request: $userRequest,
                field: 'email',
                value: $email,
            );

            if ($emailExists) {
                return ResponseDTO::error(
                    message: 'Bu e-posta adresi zaten kullanılıyor.',
                    errors: [
                        'email' => [
                            'Bu e-posta adresi zaten kullanılıyor.',
                        ],
                    ],
                    statusCode: 422,
                );
            }

            $createRequest = $userRequest->withData([
                'name' => trim((string) $data['name']),
                'email' => $email,
                'password' => Hash::make(
                    (string) $data['password']
                ),
                'role' => 'user',
                'is_active' => true,
            ]);

            $createdModel = $this->eloquentService->create(
                $createRequest
            );

            if (! $createdModel instanceof User) {
                return ResponseDTO::error(
                    message: 'Kullanıcı oluşturulamadı.',
                    errors: [],
                    statusCode: 500,
                );
            }

            $plainTextToken = $createdModel
                ->createToken('register-token')
                ->plainTextToken;

            return ResponseDTO::success(
                data: [
                    'user' => $createdModel,
                    'token' => $plainTextToken,
                    'token_type' => 'Bearer',
                ],
                message: 'Kullanıcı başarıyla oluşturuldu.',
                statusCode: 201,
            );
        } catch (Throwable $exception) {
            Log::error('Kullanıcı kaydı sırasında hata oluştu.', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return ResponseDTO::error(
                message: 'Kullanıcı oluşturulurken bir hata oluştu.',
                errors: [],
                statusCode: 500,
            );
        }
    }

    public function login(RequestDTO $request): ResponseDTO
    {
        try {
            $data = $request->data;

            $email = mb_strtolower(
                trim((string) $data['email'])
            );

            $userRequest = $request->withModel(
                new User()
            );

            $model = $this->eloquentService->firstWhere(
                request: $userRequest,
                field: 'email',
                value: $email,
            );

            if (
                ! $model instanceof User
                || ! Hash::check(
                    (string) $data['password'],
                    (string) $model->password,
                )
            ) {
                return ResponseDTO::error(
                    message: 'E-posta adresi veya şifre hatalı.',
                    errors: [
                        'credentials' => [
                            'E-posta adresi veya şifre hatalı.',
                        ],
                    ],
                    statusCode: 401,
                );
            }

            if (! (bool) $model->is_active) {
                return ResponseDTO::error(
                    message: 'Bu kullanıcı hesabı pasif durumdadır.',
                    errors: [
                        'account' => [
                            'Bu kullanıcı hesabı pasif durumdadır.',
                        ],
                    ],
                    statusCode: 403,
                );
            }

            $deviceName = trim(
                (string) ($data['device_name'] ?? 'frontend')
            );

            if ($deviceName === '') {
                $deviceName = 'frontend';
            }

            $plainTextToken = $model
                ->createToken($deviceName)
                ->plainTextToken;

            return ResponseDTO::success(
                data: [
                    'user' => $model,
                    'token' => $plainTextToken,
                    'token_type' => 'Bearer',
                ],
                message: 'Giriş başarılı.',
                statusCode: 200,
            );
        } catch (Throwable $exception) {
            Log::error('Kullanıcı girişi sırasında hata oluştu.', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return ResponseDTO::error(
                message: 'Giriş yapılırken bir hata oluştu.',
                errors: [],
                statusCode: 500,
            );
        }
    }

    public function me(User $user): ResponseDTO
    {
        return ResponseDTO::success(
            data: $user,
            message: 'Kullanıcı bilgileri getirildi.',
            statusCode: 200,
        );
    }

    public function logout(User $user): ResponseDTO
    {
        try {
            $currentToken = $user->currentAccessToken();
            if ($currentToken instanceof PersonalAccessToken) {
                PersonalAccessToken::query()
                    ->whereKey($currentToken->getKey())
                    ->delete();
            }

            return ResponseDTO::success(
                data: null,
                message: 'Çıkış başarılı.',
                statusCode: 200,
            );
        } catch (Throwable $exception) {
            Log::error('Çıkış işlemi sırasında hata oluştu.', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return ResponseDTO::error(
                message: 'Çıkış yapılırken bir hata oluştu.',
                errors: [],
                statusCode: 500,
            );
        }
    }
}
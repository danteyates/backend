<?php

declare(strict_types=1);

namespace App\Business\Contracts;

use App\DTOs\RequestDTO;
use App\DTOs\ResponseDTO;
use App\Models\User;

interface AuthBusinessInterface
{
    public function register(RequestDTO $requestDTO): ResponseDTO;

    public function login(RequestDTO $requestDTO): ResponseDTO;

    public function me(User $user): ResponseDTO;

    public function logout(User $user): ResponseDTO;
}
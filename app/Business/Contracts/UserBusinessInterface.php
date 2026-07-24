<?php

declare(strict_types=1);

namespace App\Business\Contracts;

use App\DTOs\RequestDTO;
use App\DTOs\ResponseDTO;

interface UserBusinessInterface
{
    public function get(RequestDTO $request): ResponseDTO;

    public function find(
        RequestDTO $request,
        string $id,
    ): ResponseDTO;

    public function create(
        RequestDTO $request,
    ): ResponseDTO;

    public function update(
        RequestDTO $request,
        string $id,
    ): ResponseDTO;

    public function delete(
        RequestDTO $request,
        string $id,
    ): ResponseDTO;
}
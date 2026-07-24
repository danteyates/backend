<?php

declare(strict_types=1);

namespace App\DTOs;

use JsonSerializable;

/**
 * @template TData
 */
final readonly class ResponseDto implements JsonSerializable
{
    /**
    * @param TData|null $data
    */
    public function __construct(
        public bool $success,
        public mixed $data = null,
        public ?string $message = null,
        public array $errors = [],
        public int $statusCode = 200,
        public array $meta = [],
    ) {}

    /**
    * @template TSuccessData
    *
    * @param TSuccessData|null $data
    * @return self<TSuccessData>
    */
    public static function success(mixed $data = null, ?string $message = null, int $statusCode = 200, array $meta = []): self {
        return new self(
            success: true,
            data: $data,
            message: $message,
            errors: [],
            statusCode: $statusCode,
            meta: $meta
        );
    }

    /**
    * @return self<null>
    */
    public static function error(string $message, array $errors = [], int $statusCode = 400, array $meta = []): self {
        return new self(
            success: false,
            data: null,
            message: $message,
            errors: $errors,
            statusCode: $statusCode,
            meta: $meta
        );
    }

    public function jsonSerialize() : array{
        return [
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'errors'=> $this->errors,
            'meta' => $this->meta
        ];
    }
}

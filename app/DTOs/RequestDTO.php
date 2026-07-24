<?php

declare(strict_types=1);

namespace app\DTOS;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final readonly class RequestDTO{
    public function __construct(
        public ?Model $model = null,
        public array $data = [],
        public array $filters = [],
        public array $sort = [],
        public array $includes = [],
        public ?int $page = null,
        public ?int $pageSize = null,
        public array $meta = [],
    ){
        if($this->page !== null && $this->page < 1){
            throw new InvalidArgumentException('Page must be at least 1');
        }

        if($this->pageSize !== null && ($this->pageSize < 1 || $this->pageSize > 100)){
            throw new InvalidArgumentException('Page size must be between 1 and 100');
        }
    }

    public function requireModel() : Model{
        if($this->model === null){
            throw new InvalidArgumentException('RequestDTO model is required');
       }
        return $this->model;
    }

    public function hasPagination() : bool{
        return $this->page !== null && $this->pageSize !== null;
    }

    public function hasFilters() : bool{
        return $this->filters !== [];
    }

    public function withData(array $data): self{
        return new self(
            model: $this->model,
            data: $data,
            filters: $this->filters,
            sort: $this->sort,
            includes: $this->includes,
            page: $this->page,
            pageSize: $this->pageSize,
            meta: $this->meta,
        );
    }

    public function withModel(Model $model): self{
        return new self(
            model: $model,
            data: $this->data,
            filters: $this->filters,
            sort: $this->sort,
            includes: $this->includes,
            page: $this->page,
            pageSize: $this->pageSize,
            meta: $this->meta,
        );
    }

    public function withQueryOptions(array $filters,array $sort,array $includes): self {
        return new self(
            model: $this->model,
            data: $this->data,
            filters: $filters,
            sort: $sort,
            includes: $includes,
            page: $this->page,
            pageSize: $this->pageSize,
            meta: $this->meta,
        );
    }
}
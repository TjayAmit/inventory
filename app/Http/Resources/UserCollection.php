<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'count' => $this->collection->count(),
                'total' => $this->when(isset($this->resource['total']), $this->resource['total']),
                'per_page' => $this->when(isset($this->resource['per_page']), $this->resource['per_page']),
                'current_page' => $this->when(isset($this->resource['current_page']), $this->resource['current_page']),
                'last_page' => $this->when(isset($this->resource['last_page']), $this->resource['last_page']),
                'has_more_pages' => $this->when(isset($this->resource['has_more_pages']), $this->resource['has_more_pages']),
                'from' => $this->when(isset($this->resource['from']), $this->resource['from']),
                'to' => $this->when(isset($this->resource['to']), $this->resource['to']),
            ],
            'links' => $this->when(isset($this->resource['links']), [
                'first' => $this->resource['links']['first'] ?? null,
                'last' => $this->resource['links']['last'] ?? null,
                'prev' => $this->resource['links']['prev'] ?? null,
                'next' => $this->resource['links']['next'] ?? null,
            ]),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
                'api_version' => 'v1',
            ],
        ];
    }
}

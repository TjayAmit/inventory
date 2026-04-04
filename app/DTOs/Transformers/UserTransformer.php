<?php

namespace App\DTOs\Transformers;

use App\DTOs\User\UserResponseDTO;
use App\Models\User;

class UserTransformer
{
    /**
     * Transform User model to UserResponseDTO.
     */
    public static function toResponseDTO(User $user): UserResponseDTO
    {
        return UserResponseDTO::fromModel($user);
    }

    /**
     * Transform collection of User models to array of UserResponseDTOs.
     */
    public static function toResponseDTOCollection($users): array
    {
        return collect($users)->map(function ($user) {
            return self::toResponseDTO($user);
        })->toArray();
    }

    /**
     * Transform paginated users to array format for API responses.
     */
    public static function toPaginatedArray($users): array
    {
        return [
            'data' => self::toResponseDTOCollection($users->items()),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
                'has_more_pages' => $users->hasMorePages(),
                'next_page_url' => $users->nextPageUrl(),
                'prev_page_url' => $users->previousPageUrl(),
            ],
        ];
    }

    /**
     * Transform User model to simple array for select options.
     */
    public static function toSelectArray(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->toArray(),
        ];
    }

    /**
     * Transform collection to select options array.
     */
    public static function toSelectCollection($users): array
    {
        return $users->map(function ($user) {
            return self::toSelectArray($user);
        })->toArray();
    }
}

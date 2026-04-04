<?php

namespace App\Http\Controllers\API;

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\DTOs\User\UserFiltersDTO;
use App\DTOs\Transformers\UserTransformer;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\UserStoreRequest;
use App\Http\Requests\API\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use App\Services\UserService;
use App\Services\UserRoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class UserAPIController extends Controller
{
    protected UserService $userService;
    protected UserRoleService $roleService;

    public function __construct(UserService $userService, UserRoleService $roleService)
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
        
        // Apply middleware for API authentication and rate limiting
        $this->middleware('auth');
        $this->middleware('throttle:60,1')->only(['index', 'show']);
        $this->middleware('throttle:30,1')->only(['store', 'update', 'destroy']);
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Create filters DTO from request
            $filters = new UserFiltersDTO(
                search: $request->get('search'),
                role: $request->get('role'),
                perPage: $request->get('per_page', 10),
                sortBy: $request->get('sort_by', 'created_at'),
                sortDirection: $request->get('sort_direction', 'desc')
            );

            $users = $this->userService->getUsers($filters);

            return response()->json([
                'success' => true,
                'data' => array_map(function ($dto) {
                    return $dto->toArray();
                }, UserTransformer::toResponseDTOCollection($users->items())),
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
                'filters' => $filters->toArray(),
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Failed to fetch users', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(UserStoreRequest $request): JsonResponse
    {
        try {
            $data = $request->getValidatedData();

            // Create DTO
            $dto = new CreateUserDTO(
                name: $data['name'],
                email: $data['email'],
                password: $data['password'],
                passwordConfirmation: $data['password_confirmation'],
                roles: $data['roles']
            );

            $user = $this->userService->createUser($dto);

            Log::info('User created via API', [
                'user_id' => $user->id,
                'email' => $user->email,
                'created_by' => $request->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user->toArray(),
            ], Response::HTTP_CREATED);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action',
                'error' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN);

        } catch (\Exception $e) {
            Log::error('Failed to create user via API', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'data' => $user->toArray(),
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Failed to fetch user', [
                'error' => $e->getMessage(),
                'user_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(UserUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->getValidatedData();

            // Create DTO
            $dto = new UpdateUserDTO(
                name: $data['name'],
                email: $data['email'],
                password: $data['password'] ?? null,
                passwordConfirmation: $data['password_confirmation'] ?? null,
                roles: $data['roles'],
                userId: $id
            );

            $user = $this->userService->updateUser($id, $dto);

            Log::info('User updated via API', [
                'user_id' => $user->id,
                'updated_by' => $request->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user->toArray(),
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], Response::HTTP_NOT_FOUND);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action',
                'error' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN);

        } catch (\Exception $e) {
            Log::error('Failed to update user via API', [
                'error' => $e->getMessage(),
                'user_id' => $id,
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->userService->deleteUser($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or could not be deleted',
                ], Response::HTTP_NOT_FOUND);
            }

            Log::warning('User deleted via API', [
                'user_id' => $id,
                'deleted_by' => request()->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ], Response::HTTP_OK);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action',
                'error' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN);

        } catch (\Exception $e) {
            Log::error('Failed to delete user via API', [
                'error' => $e->getMessage(),
                'user_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search users.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => ['required', 'string', 'min:2', 'max:255'],
                'role' => ['nullable', 'string', 'exists:roles,name'],
            ]);

            $users = $this->userService->searchUsers(
                $request->get('query'),
                ['role' => $request->get('role')]
            );

            return response()->json([
                'success' => true,
                'data' => array_map(function ($dto) {
                    return $dto->toArray();
                }, $users),
                'count' => count($users),
            ], Response::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let validation exceptions pass through as 422 responses
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to search users', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to search users',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->userService->getUserStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Failed to fetch user statistics', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

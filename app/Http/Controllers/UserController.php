<?php

namespace App\Http\Controllers;

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\DTOs\User\UserFiltersDTO;
use App\Models\User;
use App\Services\UserService;
use App\Services\UserRoleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected UserService $userService;
    protected UserRoleService $roleService;

    public function __construct(UserService $userService, UserRoleService $roleService)
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view users');

        // Create filters DTO from request
        $filters = new UserFiltersDTO(
            search: $request->get('search'),
            role: $request->get('role'),
            perPage: $request->get('per_page', 10),
            sortBy: $request->get('sort_by', 'created_at'),
            sortDirection: $request->get('sort_direction', 'desc')
        );

        $users = $this->userService->getUsers($filters);

        return Inertia::render('users/index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
            'roles' => Role::all(),
            'auth' => [
                'user' => $request->user()->load('roles'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create users');

        $availableRoles = $this->roleService->getAvailableRolesForUser(request()->user());

        return Inertia::render('users/create', [
            'roles' => Role::all(),
            'availableRoles' => $availableRoles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create users');

        // Validate request first
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', 'exists:roles,name'],
        ]);

        try {
            // Create DTO
            $dto = new CreateUserDTO(
                name: $validated['name'],
                email: $validated['email'],
                password: $validated['password'],
                passwordConfirmation: $request->input('password_confirmation'),
                roles: $validated['roles']
            );

            $this->userService->createUser($dto);

            return redirect()->route('users.index')
                ->with('success', 'User created successfully.');

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors([
                'roles' => $e->getMessage(),
            ])->withInput();
        } catch (\Exception $e) {
            // Log the actual error for debugging
            \Log::error('User creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->withErrors([
                'name' => 'Failed to create user. Please try again.',
            ])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view users');

        $user->load('roles', 'permissions');

        return Inertia::render('users/show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('edit users');

        $user->load('roles');
        $availableRoles = $this->roleService->getAvailableRolesForUser(request()->user());

        return Inertia::render('users/edit', [
            'user' => $user,
            'roles' => Role::all(),
            'availableRoles' => $availableRoles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('edit users');

        // Validate request first
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', 'exists:roles,name'],
        ]);

        try {
            // Create DTO
            $dto = new UpdateUserDTO(
                name: $validated['name'],
                email: $validated['email'],
                password: $validated['password'] ?? null,
                passwordConfirmation: $validated['password_confirmation'] ?? null,
                roles: $validated['roles'],
                userId: $user->id
            );

            $this->userService->updateUser($user->id, $dto);

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully.');

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withErrors([
                'name' => 'Failed to update user. Please try again.',
            ])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete users');

        try {
            $deleted = $this->userService->deleteUser($user->id);

            if ($deleted) {
                return redirect()->route('users.index')
                    ->with('success', 'User deleted successfully.');
            }

            return back()->with('error', 'Failed to delete user.');

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete user. Please try again.');
        }
    }
}

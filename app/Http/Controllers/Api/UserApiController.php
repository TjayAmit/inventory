<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = User::with(['branch', 'roles'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->has('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return UserResource::collection($users);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_active' => ['boolean'],
            'password'  => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'User created successfully.',
            'data'    => new UserResource($user->load(['branch', 'roles'])),
        ], 201);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->load(['branch', 'roles']));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'     => ['nullable', 'string', 'max:20'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_active' => ['boolean'],
            'password'  => ['nullable', 'string', 'min:8'],
        ]);

        $data = collect($validated)->except('password')->toArray();

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully.',
            'data'    => new UserResource($user->load(['branch', 'roles'])),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }
}

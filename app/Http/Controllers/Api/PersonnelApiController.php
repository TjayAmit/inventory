<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonnelRequest;
use App\Http\Resources\PersonnelResource;
use App\Models\Branch;
use App\Models\User;
use App\Services\PersonnelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PersonnelApiController extends Controller
{
    private const ROLES = ['admin', 'store_manager', 'cashier', 'warehouse_staff'];

    public function __construct(private readonly PersonnelService $personnelService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $personnel = $this->personnelService->listPersonnel($request);

        return PersonnelResource::collection($personnel);
    }

    public function store(PersonnelRequest $request): JsonResponse
    {
        $user = $this->personnelService->createStaff($request->validated());

        return response()->json([
            'message' => 'Staff member created successfully.',
            'data'    => new PersonnelResource($user),
        ], 201);
    }

    public function show(User $user): PersonnelResource
    {
        return new PersonnelResource($user->load(['branch', 'roles']));
    }

    public function update(PersonnelRequest $request, User $user): JsonResponse
    {
        $updated = $this->personnelService->updateStaff($user, $request->validated());

        return response()->json([
            'message' => 'Staff member updated successfully.',
            'data'    => new PersonnelResource($updated),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->personnelService->deleteStaff($user);

        return response()->json([
            'message' => 'Staff member deleted successfully.',
        ]);
    }

    public function assignBranch(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'role'      => 'nullable|string|in:' . implode(',', self::ROLES),
        ]);

        $updated = $this->personnelService->assignBranch(
            $user,
            (int) $validated['branch_id'],
            $validated['role'] ?? null
        );

        return response()->json([
            'message' => 'Personnel assigned to branch successfully.',
            'data'    => new PersonnelResource($updated),
        ]);
    }

    public function revokeBranch(User $user): JsonResponse
    {
        $updated = $this->personnelService->revokeBranch($user);

        return response()->json([
            'message' => 'Branch access revoked successfully.',
            'data'    => new PersonnelResource($updated),
        ]);
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'required|string|in:' . implode(',', self::ROLES),
        ]);

        $updated = $this->personnelService->assignRole($user, $validated['role']);

        return response()->json([
            'message' => 'Role assigned successfully.',
            'data'    => new PersonnelResource($updated),
        ]);
    }

    public function revokeRole(User $user): JsonResponse
    {
        $updated = $this->personnelService->revokeRole($user);

        return response()->json([
            'message' => 'Role revoked successfully.',
            'data'    => new PersonnelResource($updated),
        ]);
    }

    public function branches(): JsonResponse
    {
        $branches = Branch::select('id', 'name', 'code')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $branches]);
    }
}

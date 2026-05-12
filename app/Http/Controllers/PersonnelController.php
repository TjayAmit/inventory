<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonnelRequest;
use App\Models\Branch;
use App\Models\User;
use App\Services\PersonnelService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PersonnelController extends Controller
{
    private const ROLES = ['admin', 'store_manager', 'cashier', 'warehouse_staff'];

    public function __construct(private readonly PersonnelService $personnelService) {}

    public function index(Request $request)
    {
        $personnel = $this->personnelService->listPersonnel($request);

        $branches = Branch::select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('personnel/index', [
            'data' => $personnel,
            'branches' => $branches,
            'roles' => self::ROLES,
            'filters' => $request->only(['search', 'per_page', 'branch_id', 'role']),
        ]);
    }

    public function assignBranch(Request $request, User $user)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'role' => 'nullable|string|in:' . implode(',', self::ROLES),
        ]);

        $this->personnelService->assignBranch($user, (int) $validated['branch_id'], $validated['role'] ?? null);

        return back()->with('success', 'Personnel assigned to branch successfully.');
    }

    public function revokeBranch(User $user)
    {
        $this->personnelService->revokeBranch($user);

        return back()->with('success', 'Branch access revoked successfully.');
    }

    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|string|in:' . implode(',', self::ROLES),
        ]);

        $this->personnelService->assignRole($user, $validated['role']);

        return back()->with('success', 'Role assigned successfully.');
    }

    public function revokeRole(User $user)
    {
        $this->personnelService->revokeRole($user);

        return back()->with('success', 'Role revoked successfully.');
    }

    public function create()
    {
        $branches = Branch::select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('personnel/create', [
            'branches' => $branches,
            'roles'    => self::ROLES,
        ]);
    }

    public function store(PersonnelRequest $request)
    {
        $this->personnelService->createStaff($request->validated());

        return redirect()->route('personnel.index')->with('success', 'Staff member created successfully.');
    }

    public function edit(User $user)
    {
        $branches = Branch::select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('personnel/edit', [
            'user'     => $user->load(['branch', 'roles']),
            'branches' => $branches,
            'roles'    => self::ROLES,
        ]);
    }

    public function update(PersonnelRequest $request, User $user)
    {
        $this->personnelService->updateStaff($user, $request->validated());

        return redirect()->route('personnel.index')->with('success', 'Staff member updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->personnelService->deleteStaff($user);

        return redirect()->route('personnel.index')->with('success', 'Staff member deleted successfully.');
    }
}

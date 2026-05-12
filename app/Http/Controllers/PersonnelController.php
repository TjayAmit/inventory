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
    private const LOG = 'personnel';
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
            'data'     => $personnel,
            'branches' => $branches,
            'roles'    => self::ROLES,
            'filters'  => $request->only(['search', 'per_page', 'branch_id', 'role']),
        ]);
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
        $user = $this->personnelService->createStaff($request->validated());

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['attributes' => ['name' => $user->name, 'email' => $user->email]])
            ->event('created')
            ->log('Staff member created');

        return redirect()->route('personnel.index')
            ->with('success', 'Staff member created successfully.');
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
        $before = $user->only(['name', 'email', 'branch_id']);

        $this->personnelService->updateStaff($user, $request->validated());

        $user->refresh();

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['old' => $before, 'attributes' => $user->only(['name', 'email', 'branch_id'])])
            ->event('updated')
            ->log('Staff member updated');

        return redirect()->route('personnel.index')
            ->with('success', 'Staff member updated successfully.');
    }

    public function destroy(User $user)
    {
        $snapshot = ['name' => $user->name, 'email' => $user->email];

        $this->personnelService->deleteStaff($user);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['attributes' => $snapshot])
            ->event('deleted')
            ->log('Staff member deleted');

        return redirect()->route('personnel.index')
            ->with('success', 'Staff member deleted successfully.');
    }

    public function assignBranch(Request $request, User $user)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'role'      => 'nullable|string|in:' . implode(',', self::ROLES),
        ]);

        $this->personnelService->assignBranch($user, (int) $validated['branch_id'], $validated['role'] ?? null);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['attributes' => ['branch_id' => $validated['branch_id'], 'role' => $validated['role'] ?? null]])
            ->event('updated')
            ->log('Branch assigned to ' . $user->name);

        return back()->with('success', 'Personnel assigned to branch successfully.');
    }

    public function revokeBranch(User $user)
    {
        $this->personnelService->revokeBranch($user);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['attributes' => ['name' => $user->name]])
            ->event('updated')
            ->log('Branch access revoked from ' . $user->name);

        return back()->with('success', 'Branch access revoked successfully.');
    }

    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|string|in:' . implode(',', self::ROLES),
        ]);

        $this->personnelService->assignRole($user, $validated['role']);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['attributes' => ['role' => $validated['role']]])
            ->event('updated')
            ->log('Role "' . $validated['role'] . '" assigned to ' . $user->name);

        return back()->with('success', 'Role assigned successfully.');
    }

    public function revokeRole(User $user)
    {
        $this->personnelService->revokeRole($user);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['attributes' => ['name' => $user->name]])
            ->event('updated')
            ->log('Role revoked from ' . $user->name);

        return back()->with('success', 'Role revoked successfully.');
    }
}

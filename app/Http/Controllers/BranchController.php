<?php

namespace App\Http\Controllers;

use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use App\Models\User;
use App\Services\BranchService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BranchController extends Controller
{
    private const LOG = 'branches';
    private const FIELDS = ['name', 'code', 'address', 'city', 'phone', 'is_active', 'manager_id'];

    public function __construct(private readonly BranchService $branchService) {}

    public function index(Request $request)
    {
        return Inertia::render('branches/index', [
            'data'    => $this->branchService->list($request)->withQueryString(),
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function create()
    {
        return Inertia::render('branches/create', [
            'managers' => User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(BranchRequest $request)
    {
        $branch = $this->branchService->create($request);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($branch)
            ->withProperties(['attributes' => $branch->only(self::FIELDS)])
            ->event('created')
            ->log('created');

        return redirect()->route('branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch)
    {
        $branch->load('manager');

        return Inertia::render('branches/show', [
            'branch' => $branch,
        ]);
    }

    public function edit(Branch $branch)
    {
        $branch->load('manager');

        return Inertia::render('branches/edit', [
            'branch'   => $branch,
            'managers' => User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(BranchRequest $request, Branch $branch)
    {
        $before = $branch->only(self::FIELDS);

        $updated = $this->branchService->update($request, $branch);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($updated)
            ->withProperties(['old' => $before, 'attributes' => $updated->only(self::FIELDS)])
            ->event('updated')
            ->log('updated');

        return redirect()->route('branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        $snapshot = $branch->only(self::FIELDS);

        $this->branchService->delete($branch);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($branch)
            ->withProperties(['attributes' => $snapshot])
            ->event('deleted')
            ->log('deleted');

        return redirect()->route('branches.index')
            ->with('success', 'Branch deleted successfully.');
    }
}

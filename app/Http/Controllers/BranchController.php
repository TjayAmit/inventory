<?php

namespace App\Http\Controllers;

use App\Http\Requests\BranchRequest;
use App\Models\User;
use App\Models\Branch;
use App\Services\BranchService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BranchController extends Controller
{
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
        $this->branchService->create($request);

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
        $this->branchService->update($request, $branch);

        return redirect()->route('branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        $this->branchService->delete($branch);

        return redirect()->route('branches.index')
            ->with('success', 'Branch deleted successfully.');
    }
}

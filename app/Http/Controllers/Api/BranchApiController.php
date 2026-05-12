<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BranchApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $branches = Branch::with('manager')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->boolean('active_only'), fn ($q) => $q->active())
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return BranchResource::collection($branches);
    }

    public function store(BranchRequest $request): JsonResponse
    {
        $branch = Branch::create($request->validated());

        return response()->json([
            'message' => 'Branch created successfully.',
            'data'    => new BranchResource($branch->load('manager')),
        ], 201);
    }

    public function show(Branch $branch): BranchResource
    {
        return new BranchResource($branch->load('manager'));
    }

    public function update(BranchRequest $request, Branch $branch): JsonResponse
    {
        $branch->update($request->validated());

        return response()->json([
            'message' => 'Branch updated successfully.',
            'data'    => new BranchResource($branch->load('manager')),
        ]);
    }

    public function destroy(Branch $branch): JsonResponse
    {
        $branch->delete();

        return response()->json(['message' => 'Branch deleted successfully.']);
    }
}

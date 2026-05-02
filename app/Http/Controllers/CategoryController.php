<?php

namespace App\Http\Controllers;

use App\DTOs\Category\CreateCategoryDTO;
use App\DTOs\Category\UpdateCategoryDTO;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
        $this->middleware('auth');
        $this->middleware('permission:category.view')->only(['index', 'show']);
        $this->middleware('permission:category.create')->only(['create', 'store']);
        $this->middleware('permission:category.edit')->only(['edit', 'update']);
        $this->middleware('permission:category.delete')->only(['destroy']);
        $this->middleware('permission:category.manage')->only(['toggleStatus', 'updateSortOrder', 'move']);
    }

    /**
     * Display a listing of the categories.
     */
    public function index(Request $request): Response
    {
        $perPage = $request->get('per_page', 15);
        $categories = $this->categoryService->getPaginatedCategories($perPage);

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['per_page']),
            'can' => [
                'create' => auth()->user()->can('create', Category::class),
                'edit' => auth()->user()->can('update', Category::class),
                'delete' => auth()->user()->can('delete', Category::class),
                'manage' => auth()->user()->can('manageHierarchy', Category::class),
            ]
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): Response
    {
        $parentCategories = $this->categoryService->getCategoriesForDropdown();

        return Inertia::render('Categories/Create', [
            'parentCategories' => $parentCategories,
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        try {
            $dto = new CreateCategoryDTO(
                name: $request->validated('name'),
                description: $request->validated('description'),
                parentId: $request->validated('parent_id'),
                isActive: $request->validated('is_active', true),
                sortOrder: $request->validated('sort_order', 0)
            );

            $category = $this->categoryService->createCategory($dto);

            return redirect()
                ->route('categories.index')
                ->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create category: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category): Response
    {
        $categoryDto = $this->categoryService->getCategoryById($category->id);
        $childCategories = $this->categoryService->getChildCategories($category->id);

        return Inertia::render('Categories/Show', [
            'category' => $categoryDto,
            'childCategories' => $childCategories,
            'can' => [
                'edit' => auth()->user()->can('update', $category),
                'delete' => auth()->user()->can('delete', $category),
                'manage' => auth()->user()->can('manageHierarchy', $category),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): Response
    {
        $categoryDto = $this->categoryService->getCategoryById($category->id);
        $parentCategories = $this->categoryService->getCategoriesForDropdown($category->id);

        return Inertia::render('Categories/Edit', [
            'category' => $categoryDto,
            'parentCategories' => $parentCategories,
        ]);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        try {
            $dto = new UpdateCategoryDTO(
                name: $request->validated('name'),
                description: $request->validated('description'),
                parentId: $request->validated('parent_id'),
                isActive: $request->validated('is_active'),
                sortOrder: $request->validated('sort_order'),
                categoryId: $category->id
            );

            $updatedCategory = $this->categoryService->updateCategory($category->id, $dto);

            return redirect()
                ->route('categories.index')
                ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update category: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        try {
            $this->categoryService->deleteCategory($category->id);

            return redirect()
                ->route('categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to delete category: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle category active status.
     */
    public function toggleStatus(Category $category): RedirectResponse
    {
        try {
            $this->categoryService->toggleCategoryStatus($category->id);

            return redirect()
                ->back()
                ->with('success', 'Category status updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to update category status: ' . $e->getMessage()]);
        }
    }

    /**
     * Update sort order for categories.
     */
    public function updateSortOrder(Request $request): JsonResponse
    {
        try {
            $categoryIds = $request->validate([
                'category_ids' => 'required|array',
                'category_ids.*' => 'integer|exists:categories,id'
            ])['category_ids'];

            $result = $this->categoryService->updateCategorySortOrder($categoryIds);

            return response()->json([
                'success' => $result,
                'message' => 'Sort order updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sort order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move category to new parent.
     */
    public function move(Request $request, Category $category): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'parent_id' => 'nullable|integer|exists:categories,id'
            ]);

            $this->categoryService->moveCategory($category->id, $validated['parent_id']);

            return redirect()
                ->back()
                ->with('success', 'Category moved successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to move category: ' . $e->getMessage()]);
        }
    }

    /**
     * Search categories.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $term = $request->get('term', '');
            $limit = $request->get('limit', 10);

            $categories = $this->categoryService->searchCategories($term, $limit);

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories for dropdown.
     */
    public function dropdown(Request $request): JsonResponse
    {
        try {
            $excludeId = $request->get('exclude_id');
            $categories = $this->categoryService->getCategoriesForDropdown($excludeId);

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category tree.
     */
    public function tree(): JsonResponse
    {
        try {
            $tree = $this->categoryService->getCategoryTree();

            return response()->json([
                'success' => true,
                'data' => $tree
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get category tree: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->categoryService->getCategoryStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories with product counts.
     */
    public function withProductCounts(): JsonResponse
    {
        try {
            $categories = $this->categoryService->getCategoriesWithProductCounts();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category descendants.
     */
    public function descendants(Category $category): JsonResponse
    {
        try {
            $descendants = $this->categoryService->getCategoryDescendants($category->id);

            return response()->json([
                'success' => true,
                'data' => $descendants
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get descendants: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get root categories.
     */
    public function root(): JsonResponse
    {
        try {
            $categories = $this->categoryService->getRootCategories();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get root categories: ' . $e->getMessage()
            ], 500);
        }
    }
}

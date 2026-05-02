<?php

namespace App\Http\Controllers\API;

use App\DTOs\Category\CreateCategoryDTO;
use App\DTOs\Category\UpdateCategoryDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Category\StoreCategoryAPIRequest;
use App\Http\Requests\API\Category\UpdateCategoryAPIRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryAPIController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
        $this->middleware('auth'); // Using web auth instead of sanctum
        $this->middleware('permission:category.view')->only(['index', 'show', 'active', 'tree', 'root', 'children', 'search', 'dropdown', 'descendants', 'withProductCounts']);
        $this->middleware('permission:category.create')->only(['store']);
        $this->middleware('permission:category.edit')->only(['update']);
        $this->middleware('permission:category.delete')->only(['destroy']);
        $this->middleware('permission:category.manage')->only(['toggleStatus', 'updateSortOrder', 'move', 'statistics']);
    }

    /**
     * Display a listing of the categories.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $categories = $this->categoryService->getPaginatedCategories($perPage);

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Categories retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(StoreCategoryAPIRequest $request): JsonResponse
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

            return response()->json([
                'success' => true,
                'data' => $category,
                'message' => 'Category created successfully.'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category): JsonResponse
    {
        try {
            $categoryDto = $this->categoryService->getCategoryById($category->id);

            if (!$categoryDto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found.'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'data' => $categoryDto,
                'message' => 'Category retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified category in storage.
     */
    public function update(UpdateCategoryAPIRequest $request, Category $category): JsonResponse
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

            return response()->json([
                'success' => true,
                'data' => $updatedCategory,
                'message' => 'Category updated successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            $this->categoryService->deleteCategory($category->id);

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Get all active categories.
     */
    public function active(): JsonResponse
    {
        try {
            $categories = $this->categoryService->getActiveCategories();

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Active categories retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active categories: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get category tree structure.
     */
    public function tree(): JsonResponse
    {
        try {
            $tree = $this->categoryService->getCategoryTree();

            return response()->json([
                'success' => true,
                'data' => $tree,
                'message' => 'Category tree retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category tree: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'data' => $categories,
                'message' => 'Root categories retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve root categories: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get child categories for a given parent.
     */
    public function children(int $parentId): JsonResponse
    {
        try {
            $categories = $this->categoryService->getChildCategories($parentId);

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Child categories retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve child categories: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search categories.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $term = $request->validate([
                'term' => 'required|string|min:1',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            $limit = $term['limit'] ?? 10;
            $categories = $this->categoryService->searchCategories($term['term'], $limit);

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Categories searched successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
                'data' => $categories,
                'message' => 'Categories retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Toggle category active status.
     */
    public function toggleStatus(Category $category): JsonResponse
    {
        try {
            $updatedCategory = $this->categoryService->toggleCategoryStatus($category->id);

            return response()->json([
                'success' => true,
                'data' => $updatedCategory,
                'message' => 'Category status updated successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category status: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Update sort order for categories.
     */
    public function updateSortOrder(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'category_ids' => 'required|array',
                'category_ids.*' => 'integer|exists:categories,id'
            ]);

            $result = $this->categoryService->updateCategorySortOrder($validated['category_ids']);

            return response()->json([
                'success' => $result,
                'message' => 'Sort order updated successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sort order: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Move category to new parent.
     */
    public function move(Request $request, Category $category): JsonResponse
    {
        try {
            $validated = $request->validate([
                'parent_id' => 'nullable|integer|exists:categories,id'
            ]);

            $updatedCategory = $this->categoryService->moveCategory($category->id, $validated['parent_id']);

            return response()->json([
                'success' => true,
                'data' => $updatedCategory,
                'message' => 'Category moved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to move category: ' . $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
                'data' => $statistics,
                'message' => 'Statistics retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'data' => $categories,
                'message' => 'Categories with product counts retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'data' => $descendants,
                'message' => 'Category descendants retrieved successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve descendants: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

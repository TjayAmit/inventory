<?php

namespace App\Services\Module;

use App\DTOs\Branch\BranchData;
use App\Models\Branch;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class BranchService extends BaseService
{
    public function __construct(ModuleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getDtoClass(): string
    {
        return BranchData::class;
    }

    protected function getModelClass(): string
    {
        return Branch::class;
    }

    public function getModuleName(): string
    {
        return 'branch';
    }

    // Branch-specific business logic methods
    public function getActiveBranches()
    {
        return $this->repository->where('is_active', true)->get();
    }

    public function getMainBranch()
    {
        return $this->repository->where('is_main_branch', true)->first();
    }

    public function getBranchesByManager(int $managerId)
    {
        return $this->repository->where('manager_id', $managerId)->get();
    }

    public function getBranchesByCountry(string $country)
    {
        return $this->repository->where('country', $country)->get();
    }

    public function getBranchesByState(string $state)
    {
        return $this->repository->where('state', $state)->get();
    }

    public function getBranchesByCity(string $city)
    {
        return $this->repository->where('city', $city)->get();
    }

    public function searchBranches(string $searchTerm)
    {
        return $this->repository->where('name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('code', 'LIKE', "%{$searchTerm}%")
            ->get();
    }

    public function getBranchesWithInventory()
    {
        return $this->repository->with(['inventory'])->get();
    }

    public function getBranchesWithUsers()
    {
        return $this->repository->with(['users'])->get();
    }

    public function getBranchesWithUsersAndInventory()
    {
        return $this->repository->with(['users', 'inventory'])->get();
    }

    public function getBranchesCount(): int
    {
        return $this->repository->count();
    }

    public function getActiveBranchesCount(): int
    {
        return $this->repository->where('is_active', true)->count();
    }

    public function getInactiveBranchesCount(): int
    {
        return $this->repository->where('is_active', false)->count();
    }

    public function getMainBranchesCount(): int
    {
        return $this->repository->where('is_main_branch', true)->count();
    }

    public function getBranchesByTimezone(string $timezone)
    {
        return $this->repository->where('timezone', $timezone)->get();
    }

    public function getBranchesByCurrency(string $currency)
    {
        return $this->repository->where('currency', $currency)->get();
    }

    public function isBranchCodeUnique(string $code, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('code', $code);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function getBranchesWithInventoryCount()
    {
        return $this->repository->with(['inventory'])
            ->selectRaw('branches.*, COUNT(inventory.id) as inventory_count')
            ->groupBy('branches.id')
            ->get();
    }
}

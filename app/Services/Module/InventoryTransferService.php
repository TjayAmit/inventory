<?php

namespace App\Services\Module;

use App\DTOs\InventoryTransfer\InventoryTransferData;
use App\Models\InventoryTransfer;
use App\Repositories\Contracts\Module\ModuleRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class InventoryTransferService extends BaseService
{
    public function __construct(ModuleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getDtoClass(): string
    {
        return InventoryTransferData::class;
    }

    protected function getModelClass(): string
    {
        return InventoryTransfer::class;
    }

    public function getModuleName(): string
    {
        return 'inventory_transfer';
    }

    // Inventory transfer-specific business logic methods
    public function getActiveTransfers()
    {
        return $this->repository->where('status', '!=', 'cancelled')->get();
    }

    public function getDraftTransfers()
    {
        return $this->repository->where('status', 'draft')->get();
    }

    public function getPendingApprovalTransfers()
    {
        return $this->repository->where('status', 'pending_approval')->get();
    }

    public function getApprovedTransfers()
    {
        return $this->repository->where('status', 'approved')->get();
    }

    public function getInTransitTransfers()
    {
        return $this->repository->where('status', 'in_transit')->get();
    }

    public function getCompletedTransfers()
    {
        return $this->repository->where('status', 'completed')->get();
    }

    public function getCancelledTransfers()
    {
        return $this->repository->where('status', 'cancelled')->get();
    }

    public function getTransfersByProduct(int $productId)
    {
        return $this->repository->where('product_id', $productId)->get();
    }

    public function getTransfersBySourceBranch(int $sourceBranchId)
    {
        return $this->repository->where('source_branch_id', $sourceBranchId)->get();
    }

    public function getTransfersByDestinationBranch(int $destinationBranchId)
    {
        return $this->repository->where('destination_branch_id', $destinationBranchId)->get();
    }

    public function getTransfersByCreator(int $createdBy)
    {
        return $this->repository->where('created_by', $createdBy)->get();
    }

    public function getTransfersByApprover(int $approvedBy)
    {
        return $this->repository->where('approved_by', $approvedBy)->get();
    }

    public function getTransfersByReceiver(int $receivedBy)
    {
        return $this->repository->where('received_by', $receivedBy)->get();
    }

    public function getTransfersByDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('transfer_date', [$startDate, $endDate])->get();
    }

    public function getTransfersByExpectedDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('expected_date', [$startDate, $endDate])->get();
    }

    public function getTransfersByReceivedDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('received_date', [$startDate, $endDate])->get();
    }

    public function getTransfersByQuantityRange(int $minQuantity, int $maxQuantity)
    {
        return $this->repository->whereBetween('quantity', [$minQuantity, $maxQuantity])->get();
    }

    public function getTransfersByReason(string $reason)
    {
        return $this->repository->where('reason', 'LIKE', "%{$reason}%")->get();
    }

    public function getTransfersWithProduct()
    {
        return $this->repository->with(['product'])->get();
    }

    public function getTransfersWithSourceBranch()
    {
        return $this->repository->with(['sourceBranch'])->get();
    }

    public function getTransfersWithDestinationBranch()
    {
        return $this->repository->with(['destinationBranch'])->get();
    }

    public function getTransfersWithSourceAndDestinationBranch()
    {
        return $this->repository->with(['sourceBranch', 'destinationBranch'])->get();
    }

    public function getTransfersWithProductAndBranches()
    {
        return $this->repository->with(['product', 'sourceBranch', 'destinationBranch'])->get();
    }

    public function getTransfersWithCreator()
    {
        return $this->repository->with(['creator'])->get();
    }

    public function getTransfersWithApprover()
    {
        return $this->repository->with(['approver'])->get();
    }

    public function getTransfersWithReceiver()
    {
        return $this->repository->with(['receiver'])->get();
    }

    public function getTransfersWithAllRelations()
    {
        return $this->repository->with(['product', 'sourceBranch', 'destinationBranch', 'creator', 'approver', 'receiver'])->get();
    }

    public function getTransfersCount(): int
    {
        return $this->repository->count();
    }

    public function getActiveTransfersCount(): int
    {
        return $this->repository->where('status', '!=', 'cancelled')->count();
    }

    public function getDraftTransfersCount(): int
    {
        return $this->repository->where('status', 'draft')->count();
    }

    public function getPendingApprovalTransfersCount(): int
    {
        return $this->repository->where('status', 'pending_approval')->count();
    }

    public function getApprovedTransfersCount(): int
    {
        return $this->repository->where('status', 'approved')->count();
    }

    public function getInTransitTransfersCount(): int
    {
        return $this->repository->where('status', 'in_transit')->count();
    }

    public function getCompletedTransfersCount(): int
    {
        return $this->repository->where('status', 'completed')->count();
    }

    public function getCancelledTransfersCount(): int
    {
        return $this->repository->where('status', 'cancelled')->count();
    }

    public function getTotalTransferredQuantity(): int
    {
        return $this->repository->sum('quantity');
    }

    public function getTotalReceivedQuantity(): int
    {
        return $this->repository->sum('quantity_received');
    }

    public function getTotalPendingQuantity(): int
    {
        return $this->repository->sumRaw('quantity - quantity_received');
    }

    public function approveTransfer(int $transferId, int $approvedBy): bool
    {
        return $this->repository->update($transferId, [
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    public function startTransfer(int $transferId): bool
    {
        return $this->repository->update($transferId, [
            'status' => 'in_transit',
            'transfer_date' => now(),
        ]);
    }

    public function completeTransfer(int $transferId, int $quantityReceived, int $receivedBy): bool
    {
        return $this->repository->update($transferId, [
            'status' => 'completed',
            'quantity_received' => $quantityReceived,
            'received_by' => $receivedBy,
            'received_date' => now(),
        ]);
    }

    public function cancelTransfer(int $transferId): bool
    {
        return $this->repository->update($transferId, ['status' => 'cancelled']);
    }

    public function isTransferNumberUnique(string $transferNumber, ?int $excludeId = null): bool
    {
        $query = $this->repository->where('transfer_number', $transferNumber);
        
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    public function getTransfersBySourceAndDestination(int $sourceBranchId, int $destinationBranchId)
    {
        return $this->repository->where('source_branch_id', $sourceBranchId)
            ->where('destination_branch_id', $destinationBranchId)
            ->get();
    }

    public function getTransfersBySourceAndDateRange(int $sourceBranchId, string $startDate, string $endDate)
    {
        return $this->repository->where('source_branch_id', $sourceBranchId)
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->get();
    }

    public function getTransfersByDestinationAndDateRange(int $destinationBranchId, string $startDate, string $endDate)
    {
        return $this->repository->where('destination_branch_id', $destinationBranchId)
            ->whereBetween('received_date', [$startDate, $endDate])
            ->get();
    }

    public function getTransfersByProductAndDateRange(int $productId, string $startDate, string $endDate)
    {
        return $this->repository->where('product_id', $productId)
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->get();
    }

    public function getTransfersByCreatorAndDateRange(int $createdBy, string $startDate, string $endDate)
    {
        return $this->repository->where('created_by', $createdBy)
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->get();
    }

    public function getTransfersByNotes(string $notes)
    {
        return $this->repository->where('notes', 'LIKE', "%{$notes}%")->get();
    }

    public function getTransfersWithNotes()
    {
        return $this->repository->whereNotNull('notes')->get();
    }

    public function getTransfersWithoutNotes()
    {
        return $this->repository->whereNull('notes')->get();
    }

    public function getTransfersByApprovalNotes(string $approvalNotes)
    {
        return $this->repository->where('approval_notes', 'LIKE', "%{$approvalNotes}%")->get();
    }

    public function getTransfersByReceivingNotes(string $receivingNotes)
    {
        return $this->repository->where('receiving_notes', 'LIKE', "%{$receivingNotes}%")->get();
    }

    public function getTransfersByBatchNumber(string $batchNumber)
    {
        return $this->repository->where('batch_number', $batchNumber)->get();
    }

    public function getTransfersByBatchNumbers(array $batchNumbers)
    {
        return $this->repository->whereIn('batch_number', $batchNumbers)->get();
    }

    public function getTransfersByTransferDate(string $transferDate)
    {
        return $this->repository->where('transfer_date', $transferDate)->get();
    }

    public function getTransfersByExpectedDate(string $expectedDate)
    {
        return $this->repository->where('expected_date', $expectedDate)->get();
    }

    public function getTransfersByReceivedDate(string $receivedDate)
    {
        return $this->repository->where('received_date', $receivedDate)->get();
    }

    public function getTransfersByQuantity(int $quantity)
    {
        return $this->repository->where('quantity', $quantity)->get();
    }

    public function getTransfersByQuantityReceived(int $quantityReceived)
    {
        return $this->repository->where('quantity_received', $quantityReceived)->get();
    }

    public function getTransfersByQuantityDifference(int $difference)
    {
        return $this->repository->whereRaw('quantity - quantity_received = ?', [$difference])->get();
    }

    public function getTransfersByQuantityDifferenceRange(int $minDifference, int $maxDifference)
    {
        return $this->repository->whereRaw('(quantity - quantity_received) BETWEEN ? AND ?', [$minDifference, $maxDifference])->get();
    }

    public function getTransfersWithQuantityDifference()
    {
        return $this->repository->selectRaw('*, (quantity - quantity_received) as quantity_difference')
            ->havingRaw('quantity_difference != 0')
            ->get();
    }

    public function getTransfersWithShortage()
    {
        return $this->repository->whereRaw('quantity_received < quantity')->get();
    }

    public function getTransfersWithExcess()
    {
        return $this->repository->whereRaw('quantity_received > quantity')->get();
    }

    public function getTransfersByTransferType(string $transferType)
    {
        return $this->repository->where('transfer_type', $transferType)->get();
    }

    public function getStockTransfers()
    {
        return $this->repository->where('transfer_type', 'stock_transfer')->get();
    }

    public function getReturnTransfers()
    {
        return $this->repository->where('transfer_type', 'return')->get();
    }

    public function getAdjustmentTransfers()
    {
        return $this->repository->where('transfer_type', 'adjustment')->get();
    }

    public function getTransfersByPriority(string $priority)
    {
        return $this->repository->where('priority', $priority)->get();
    }

    public function getHighPriorityTransfers()
    {
        return $this->repository->where('priority', 'high')->get();
    }

    public function getMediumPriorityTransfers()
    {
        return $this->repository->where('priority', 'medium')->get();
    }

    public function getLowPriorityTransfers()
    {
        return $this->repository->where('priority', 'low')->get();
    }

    public function getTransfersByCostRange(float $minCost, float $maxCost)
    {
        return $this->repository->whereBetween('total_cost', [$minCost, $maxCost])->get();
    }

    public function getTransfersByTotalCost(float $totalCost)
    {
        return $this->repository->where('total_cost', $totalCost)->get();
    }

    public function getTotalTransferCost(): float
    {
        return $this->repository->sum('total_cost');
    }

    public function getAverageTransferCost(): float
    {
        return $this->repository->avg('total_cost');
    }

    public function getTransfersByUnitCostRange(float $minCost, float $maxCost)
    {
        return $this->repository->whereBetween('unit_cost', [$minCost, $maxCost])->get();
    }

    public function getTransfersByUnitCost(float $unitCost)
    {
        return $this->repository->where('unit_cost', $unitCost)->get();
    }

    public function getTransfersByCreatorAndDateRange(int $createdBy, string $startDate, string $endDate)
    {
        return $this->repository->where('created_by', $createdBy)
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->get();
    }

    public function getTransfersByApproverAndDateRange(int $approvedBy, string $startDate, string $endDate)
    {
        return $this->repository->where('approved_by', $approvedBy)
            ->whereBetween('approved_at', [$startDate, $endDate])
            ->get();
    }

    public function getTransfersByReceiverAndDateRange(int $receivedBy, string $startDate, string $endDate)
    {
        return $this->repository->where('received_by', $receivedBy)
            ->whereBetween('received_date', [$startDate, $endDate])
            ->get();
    }

    public function getTransfersByProductAndDateRange(int $productId, string $startDate, string $endDate)
    {
        return $this->repository->where('product_id', $productId)
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->get();
    }

    public function getTransfersBySourceAndDestinationAndDateRange(int $sourceBranchId, int $destinationBranchId, string $startDate, string $endDate)
    {
        return $this->repository->where('source_branch_id', $sourceBranchId)
            ->where('destination_branch_id', $destinationBranchId)
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->get();
    }

    public function getTransfersByBatchAndDateRange(string $batchNumber, string $startDate, string $endDate)
    {
        return $this->repository->where('batch_number', $batchNumber)
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->get();
    }

    public function getTransfersByNotesAndDateRange(string $notes, string $startDate, string $endDate)
    {
        return $this->repository->where('notes', 'LIKE', "%{$notes}%")
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->get();
    }

    public function getTransfersByApprovalNotesAndDateRange(string $approvalNotes, string $startDate, string $endDate)
    {
        return $this->repository->where('approval_notes', 'LIKE', "%{$approvalNotes}%")
            ->whereBetween('approved_at', [$startDate, $endDate])
            ->get();
    }

    public function getTransfersByReceivingNotesAndDateRange(string $receivingNotes, string $startDate, string $endDate)
    {
        return $this->repository->where('receiving_notes', 'LIKE', "%{$receivingNotes}%")
            ->whereBetween('received_date', [$startDate, $endDate])
            ->get();
    }

    public function getTransfersByReasonAndDateRange(string $reason, string $startDate, string $endDate)
    {
        return $this->repository->where('reason', 'LIKE', "%{$reason}%")
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->get();
    }

    public function getTransfersByTransferDate(string $transferDate)
    {
        return $this->repository->where('transfer_date', $transferDate)->get();
    }

    public function getTransfersByTransferDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('transfer_date', [$startDate, $endDate])->get();
    }

    public function getTransfersByExpectedDate(string $expectedDate)
    {
        return $this->repository->where('expected_date', $expectedDate)->get();
    }

    public function getTransfersByExpectedDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('expected_date', [$startDate, $endDate])->get();
    }

    public function getTransfersByReceivedDate(string $receivedDate)
    {
        return $this->repository->where('received_date', $receivedDate)->get();
    }

    public function getTransfersByReceivedDateRange(string $startDate, string $endDate)
    {
        return $this->repository->whereBetween('received_date', [$startDate, $endDate])->get();
    }

    public function getTransfersByQuantity(int $quantity)
    {
        return $this->repository->where('quantity', $quantity)->get();
    }

    public function getTransfersByQuantityRange(int $minQuantity, int $maxQuantity)
    {
        return $this->repository->whereBetween('quantity', [$minQuantity, $maxQuantity])->get();
    }

    public function getTransfersByQuantityReceived(int $quantityReceived)
    {
        return $this->repository->where('quantity_received', $quantityReceived)->get();
    }

    public function getTransfersByQuantityReceivedRange(int $minQuantity, int $maxQuantity)
    {
        return $this->repository->whereBetween('quantity_received', [$minQuantity, $maxQuantity])->get();
    }

    public function getTransfersByQuantityDifference(int $difference)
    {
        return $this->repository->whereRaw('quantity - quantity_received = ?', [$difference])->get();
    }

    public function getTransfersByQuantityDifferenceRange(int $minDifference, int $maxDifference)
    {
        return $this->repository->whereRaw('(quantity - quantity_received) BETWEEN ? AND ?', [$minDifference, $maxDifference])->get();
    }

    public function getTransfersWithQuantityDifference()
    {
        return $this->repository->selectRaw('*, (quantity - quantity_received) as quantity_difference')
            ->havingRaw('quantity_difference != 0')
            ->get();
    }

    public function getTransfersWithShortage()
    {
        return $this->repository->whereRaw('quantity_received < quantity')->get();
    }

    public function getTransfersWithExcess()
    {
        return $this->repository->whereRaw('quantity_received > quantity')->get();
    }

    public function getTransfersByTransferType(string $transferType)
    {
        return $this->repository->where('transfer_type', $transferType)->get();
    }

    public function getStockTransfers()
    {
        return $this->repository->where('transfer_type', 'stock_transfer')->get();
    }

    public function getReturnTransfers()
    {
        return $this->repository->where('transfer_type', 'return')->get();
    }

    public function getAdjustmentTransfers()
    {
        return $this->repository->where('transfer_type', 'adjustment')->get();
    }

    public function getTransfersByPriority(string $priority)
    {
        return $this->repository->where('priority', $priority)->get();
    }

    public function getHighPriorityTransfers()
    {
        return $this->repository->where('priority', 'high')->get();
    }

    public function getMediumPriorityTransfers()
    {
        return $this->repository->where('priority', 'medium')->get();
    }

    public function getLowPriorityTransfers()
    {
        return $this->repository->where('priority', 'low')->get();
    }

    public function getTransfersByCostRange(float $minCost, float $maxCost)
    {
        return $this->repository->whereBetween('total_cost', [$minCost, $maxCost])->get();
    }

    public function getTransfersByTotalCost(float $totalCost)
    {
        return $this->repository->where('total_cost', $totalCost)->get();
    }

    public function getTotalTransferCost(): float
    {
        return $this->repository->sum('total_cost');
    }

    public function getAverageTransferCost(): float
    {
        return $this->repository->avg('total_cost');
    }

    public function getTransfersByUnitCostRange(float $minCost, float $maxCost)
    {
        return $this->repository->whereBetween('unit_cost', [$minCost, $maxCost])->get();
    }

    public function getTransfersByUnitCost(float $unitCost)
    {
        return $this->repository->where('unit_cost', $unitCost)->get();
    }
}

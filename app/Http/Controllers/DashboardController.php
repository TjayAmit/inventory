<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $today      = now()->toDateString();
        $yesterday  = now()->subDay()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $lastMonthStart = now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd   = now()->subMonth()->endOfMonth()->toDateString();

        $done = ['paid', 'completed'];

        // ── Today ──────────────────────────────────────────────────
        $todayRevenue = (float) SalesOrder::whereIn('status', $done)
            ->whereDate('order_date', $today)->sum('total_amount');
        $todayCount   = SalesOrder::whereIn('status', $done)
            ->whereDate('order_date', $today)->count();

        // ── Yesterday (for trend) ──────────────────────────────────
        $yesterdayRevenue = (float) SalesOrder::whereIn('status', $done)
            ->whereDate('order_date', $yesterday)->sum('total_amount');
        $yesterdayCount   = SalesOrder::whereIn('status', $done)
            ->whereDate('order_date', $yesterday)->count();

        // ── This month ─────────────────────────────────────────────
        $monthRevenue = (float) SalesOrder::whereIn('status', $done)
            ->whereDate('order_date', '>=', $monthStart)->sum('total_amount');
        $monthCount   = SalesOrder::whereIn('status', $done)
            ->whereDate('order_date', '>=', $monthStart)->count();

        // ── Last month (for trend) ─────────────────────────────────
        $lastMonthRevenue = (float) SalesOrder::whereIn('status', $done)
            ->whereDate('order_date', '>=', $lastMonthStart)
            ->whereDate('order_date', '<=', $lastMonthEnd)
            ->sum('total_amount');

        // ── 7-day sparkline ────────────────────────────────────────
        $weekData = collect(range(6, 0))->map(function ($daysAgo) use ($done) {
            $date = now()->subDays($daysAgo);
            return [
                'label'   => $date->format('D'),
                'date'    => $date->toDateString(),
                'revenue' => (float) SalesOrder::whereIn('status', $done)
                    ->whereDate('order_date', $date->toDateString())
                    ->sum('total_amount'),
            ];
        })->values();

        // ── Catalog & stock ────────────────────────────────────────
        $totalProducts   = Product::where('is_active', true)->count();
        $activeBranches  = Branch::where('is_active', true)->count();
        $lowStockCount   = Inventory::lowStock()->where('quantity_on_hand', '>', 0)->count();
        $outOfStockCount = Inventory::outOfStock()->count();

        // ── Recent transactions ────────────────────────────────────
        $columns = ['id', 'order_number', 'order_date', 'order_time', 'status',
                    'total_amount', 'payment_status', 'branch_id', 'cashier_id'];
        if (Schema::hasColumn('sales_orders', 'payment_method')) {
            $columns[] = 'payment_method';
        }

        $recentTransactions = SalesOrder::with(['branch:id,name', 'cashier:id,name'])
            ->whereIn('status', ['paid', 'completed', 'cancelled', 'refunded'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get($columns);

        // ── Low stock items ────────────────────────────────────────
        $lowStockItems = Inventory::with('product:id,name,sku,reorder_level')
            ->lowStock()
            ->orderBy('quantity_on_hand')
            ->limit(8)
            ->get(['id', 'product_id', 'quantity_on_hand'])
            ->map(fn ($inv) => [
                'id'            => $inv->id,
                'product_name'  => $inv->product->name,
                'sku'           => $inv->product->sku,
                'quantity'      => $inv->quantity_on_hand,
                'reorder_level' => $inv->product->reorder_level,
            ]);

        return Inertia::render('dashboard', [
            'stats' => [
                'today_revenue'     => $todayRevenue,
                'today_count'       => $todayCount,
                'yesterday_revenue' => $yesterdayRevenue,
                'yesterday_count'   => $yesterdayCount,
                'month_revenue'     => $monthRevenue,
                'month_count'       => $monthCount,
                'last_month_revenue'=> $lastMonthRevenue,
                'total_products'    => $totalProducts,
                'active_branches'   => $activeBranches,
                'low_stock_count'   => $lowStockCount,
                'out_of_stock_count'=> $outOfStockCount,
            ],
            'week_data'           => $weekData,
            'recent_transactions' => $recentTransactions,
            'low_stock_items'     => $lowStockItems,
        ]);
    }
}

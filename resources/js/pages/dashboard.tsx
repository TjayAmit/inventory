import { Head, router, usePage } from '@inertiajs/react';
import {
    TrendingUp, TrendingDown, Package, Building2,
    AlertTriangle, XCircle, ArrowRight, Banknote,
    Smartphone, CreditCard, CheckCircle2,
    Minus, ShoppingBag,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { dashboard } from '@/routes';
import { show as txShow, index as txIndex } from '@/routes/transactions';

// ─── Types ────────────────────────────────────────────────────────────────────

type PaymentMethod = 'cash' | 'gcash' | 'maya';

type Stats = {
    today_revenue:      number;
    today_count:        number;
    yesterday_revenue:  number;
    yesterday_count:    number;
    month_revenue:      number;
    month_count:        number;
    last_month_revenue: number;
    total_products:     number;
    active_branches:    number;
    low_stock_count:    number;
    out_of_stock_count: number;
};

type WeekDay = { label: string; date: string; revenue: number };

type RecentTransaction = {
    id:             number;
    order_number:   string;
    order_date:     string;
    order_time:     string;
    status:         string;
    total_amount:   string;
    payment_method: PaymentMethod | null;
    branch:         { id: number; name: string } | null;
    cashier:        { id: number; name: string } | null;
};

type LowStockItem = {
    id:            number;
    product_name:  string;
    sku:           string;
    quantity:      number;
    reorder_level: number;
};

type Props = {
    stats:               Stats;
    week_data:           WeekDay[];
    recent_transactions: RecentTransaction[];
    low_stock_items:     LowStockItem[];
};

// ─── Helpers ──────────────────────────────────────────────────────────────────

const peso = (v: number) =>
    `₱${v.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

function trendPct(current: number, prev: number): number | null {
    if (prev === 0) return null;
    return Math.round(((current - prev) / prev) * 100);
}

function greet(name: string) {
    const h = new Date().getHours();
    const salutation = h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
    return `${salutation}, ${name.split(' ')[0]}`;
}

const PM_META: Record<PaymentMethod, { icon: React.ReactNode; label: string }> = {
    cash:  { icon: <Banknote  className="h-3.5 w-3.5" />, label: 'Cash'  },
    gcash: { icon: <Smartphone className="h-3.5 w-3.5" />, label: 'GCash' },
    maya:  { icon: <CreditCard className="h-3.5 w-3.5" />, label: 'Maya'  },
};

const TX_STATUS: Record<string, { dot: string; label: string }> = {
    paid:      { dot: 'bg-emerald-500', label: 'Paid'      },
    completed: { dot: 'bg-emerald-500', label: 'Completed' },
    cancelled: { dot: 'bg-red-500',     label: 'Cancelled' },
    refunded:  { dot: 'bg-orange-400',  label: 'Refunded'  },
    pending:   { dot: 'bg-yellow-400',  label: 'Pending'   },
};

// ─── Sparkline (SVG area chart, no library) ───────────────────────────────────

function Sparkline({ data }: { data: number[] }) {
    const w = 100;
    const h = 32;
    const max = Math.max(...data, 1);
    const pts = data.map((v, i) => [
        (i / Math.max(data.length - 1, 1)) * w,
        h - 2 - ((v / max) * (h - 4)),
    ]);
    const line = pts.map(([x, y], i) => `${i === 0 ? 'M' : 'L'}${x.toFixed(1)},${y.toFixed(1)}`).join(' ');
    const area = `${line} L${w},${h} L0,${h} Z`;

    return (
        <svg width={w} height={h} viewBox={`0 0 ${w} ${h}`} preserveAspectRatio="none" className="overflow-visible">
            <defs>
                <linearGradient id="sg" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stopColor="currentColor" stopOpacity="0.18" />
                    <stop offset="100%" stopColor="currentColor" stopOpacity="0" />
                </linearGradient>
            </defs>
            <path d={area} fill="url(#sg)" className="text-blue-500" />
            <path d={line} fill="none" stroke="currentColor" strokeWidth="1.5"
                strokeLinecap="round" strokeLinejoin="round" className="text-blue-500" />
        </svg>
    );
}

// ─── Trend Badge ──────────────────────────────────────────────────────────────

function TrendBadge({ current, prev, label }: { current: number; prev: number; label: string }) {
    const pct = trendPct(current, prev);

    if (pct === null) {
        return <span className="text-xs text-muted-foreground">{label}</span>;
    }
    if (pct === 0) {
        return (
            <span className="inline-flex items-center gap-1 text-xs text-muted-foreground">
                <Minus className="h-3 w-3" /> No change {label}
            </span>
        );
    }
    const up = pct > 0;
    return (
        <span className={`inline-flex items-center gap-1 text-xs font-medium ${up ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'}`}>
            {up ? <TrendingUp className="h-3 w-3" /> : <TrendingDown className="h-3 w-3" />}
            {Math.abs(pct)}% {label}
        </span>
    );
}

// ─── Dashboard ────────────────────────────────────────────────────────────────

export default function Dashboard({ stats, week_data, recent_transactions, low_stock_items }: Props) {
    const { auth } = usePage().props;
    const userName  = (auth as any)?.user?.name ?? 'there';

    const alertCount  = stats.low_stock_count + stats.out_of_stock_count;
    const outOfStock  = low_stock_items.filter(i => i.quantity <= 0);
    const lowStock    = low_stock_items.filter(i => i.quantity > 0);
    const sparkValues = week_data.map(d => d.revenue);

    const dateStr = new Date().toLocaleDateString('en-PH', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    });

    return (
        <>
            <Head title="Dashboard" />
            <div className="min-h-full bg-muted/30 p-4 lg:p-6 flex flex-col gap-5">

                {/* ── 1. Header ── */}
                <div className="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div>
                        <h1 className="text-xl font-semibold tracking-tight">{greet(userName)}</h1>
                        <p className="text-sm text-muted-foreground mt-0.5">{dateStr}</p>
                    </div>
                    {alertCount > 0 && (
                        <div className={`flex items-center gap-2 self-start text-sm font-medium px-3 py-1.5 rounded-lg border ${
                            stats.out_of_stock_count > 0
                                ? 'bg-red-50 border-red-200 text-red-700 dark:bg-red-950/40 dark:border-red-800 dark:text-red-300'
                                : 'bg-amber-50 border-amber-200 text-amber-700 dark:bg-amber-950/40 dark:border-amber-800 dark:text-amber-300'
                        }`}>
                            <AlertTriangle className="h-3.5 w-3.5 shrink-0" />
                            {stats.out_of_stock_count > 0
                                ? `${stats.out_of_stock_count} item${stats.out_of_stock_count !== 1 ? 's' : ''} out of stock`
                                : `${alertCount} item${alertCount !== 1 ? 's' : ''} need restocking`}
                        </div>
                    )}
                </div>

                {/* ── 2. Primary KPI Row ── */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {/* Today's Revenue */}
                    <div className="bg-background rounded-xl border p-5 flex flex-col gap-4">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Today's Revenue</span>
                            <span className="text-xs text-muted-foreground bg-muted rounded-full px-2.5 py-0.5">
                                {stats.today_count} sale{stats.today_count !== 1 ? 's' : ''}
                            </span>
                        </div>
                        <div>
                            <p className="text-4xl font-bold tracking-tight tabular-nums">
                                {peso(stats.today_revenue)}
                            </p>
                        </div>
                        <div className="flex items-center justify-between border-t pt-3 mt-auto">
                            <TrendBadge current={stats.today_revenue} prev={stats.yesterday_revenue} label="vs yesterday" />
                            <span className="text-xs text-muted-foreground">
                                Yesterday: {peso(stats.yesterday_revenue)}
                            </span>
                        </div>
                    </div>

                    {/* This Month */}
                    <div className="bg-background rounded-xl border p-5 flex flex-col gap-4">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-widest text-muted-foreground">This Month</span>
                            <span className="text-xs text-muted-foreground bg-muted rounded-full px-2.5 py-0.5">
                                {stats.month_count} sale{stats.month_count !== 1 ? 's' : ''}
                            </span>
                        </div>
                        <div className="flex items-end justify-between gap-3">
                            <p className="text-4xl font-bold tracking-tight tabular-nums">
                                {peso(stats.month_revenue)}
                            </p>
                            {/* 7-day sparkline */}
                            <div className="mb-1 shrink-0">
                                <Sparkline data={sparkValues} />
                                <p className="text-[10px] text-muted-foreground text-right mt-0.5">7-day</p>
                            </div>
                        </div>
                        <div className="flex items-center justify-between border-t pt-3 mt-auto">
                            <TrendBadge current={stats.month_revenue} prev={stats.last_month_revenue} label="vs last month" />
                            <span className="text-xs text-muted-foreground">
                                Last month: {peso(stats.last_month_revenue)}
                            </span>
                        </div>
                    </div>
                </div>

                {/* ── 3. Secondary Stats Strip ── */}
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    {[
                        { label: 'Active Products', value: stats.total_products,  icon: Package,       accent: '' },
                        { label: 'Active Branches', value: stats.active_branches, icon: Building2,     accent: '' },
                        {
                            label: 'Low Stock',
                            value: stats.low_stock_count,
                            icon: AlertTriangle,
                            accent: stats.low_stock_count > 0 ? 'text-amber-600 dark:text-amber-400' : '',
                        },
                        {
                            label: 'Out of Stock',
                            value: stats.out_of_stock_count,
                            icon: XCircle,
                            accent: stats.out_of_stock_count > 0 ? 'text-red-600 dark:text-red-400' : '',
                        },
                    ].map(({ label, value, icon: Icon, accent }) => (
                        <div key={label} className="bg-background rounded-xl border px-4 py-3.5 flex items-center gap-3">
                            <Icon className={`h-4 w-4 shrink-0 text-muted-foreground ${accent}`} />
                            <div className="min-w-0">
                                <p className={`text-lg font-bold tabular-nums leading-none ${accent}`}>{value}</p>
                                <p className="text-xs text-muted-foreground mt-0.5 truncate">{label}</p>
                            </div>
                        </div>
                    ))}
                </div>

                {/* ── 4. Main Content ── */}
                <div className="grid grid-cols-1 lg:grid-cols-5 gap-4 items-start">

                    {/* Recent Transactions (3/5) */}
                    <div className="lg:col-span-3 bg-background rounded-xl border overflow-hidden">
                        <div className="flex items-center justify-between px-5 py-4">
                            <div className="flex items-center gap-2">
                                <ShoppingBag className="h-4 w-4 text-muted-foreground" />
                                <h2 className="text-sm font-semibold">Recent Transactions</h2>
                            </div>
                            <Button
                                variant="ghost" size="sm"
                                className="h-7 text-xs gap-1.5 text-muted-foreground -mr-2"
                                onClick={() => router.get(txIndex())}
                            >
                                View all <ArrowRight className="h-3 w-3" />
                            </Button>
                        </div>
                        <Separator />

                        {recent_transactions.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-14 text-center">
                                <ShoppingBag className="h-9 w-9 text-muted-foreground/25 mb-3" />
                                <p className="text-sm font-medium text-muted-foreground">No transactions yet</p>
                                <p className="text-xs text-muted-foreground/70 mt-1">Completed sales will appear here</p>
                            </div>
                        ) : (
                            <>
                                {/* Column headers */}
                                <div className="grid grid-cols-[1fr_auto_auto] sm:grid-cols-[1fr_140px_auto_auto] gap-x-4 px-5 py-2 bg-muted/40 border-b">
                                    <span className="text-xs font-medium text-muted-foreground">Reference</span>
                                    <span className="hidden sm:block text-xs font-medium text-muted-foreground">Branch · Cashier</span>
                                    <span className="text-xs font-medium text-muted-foreground text-right">Amount</span>
                                    <span className="text-xs font-medium text-muted-foreground text-right">Status</span>
                                </div>

                                <div className="divide-y">
                                    {recent_transactions.map((tx) => {
                                        const meta = TX_STATUS[tx.status] ?? { dot: 'bg-muted-foreground', label: tx.status };
                                        return (
                                            <button
                                                key={tx.id}
                                                className="w-full grid grid-cols-[1fr_auto_auto] sm:grid-cols-[1fr_140px_auto_auto] gap-x-4 items-center px-5 py-3 hover:bg-muted/40 transition-colors text-left"
                                                onClick={() => router.get(txShow(tx.id))}
                                            >
                                                <div className="min-w-0">
                                                    <p className="text-sm font-mono font-medium truncate">{tx.order_number}</p>
                                                    <p className="text-xs text-muted-foreground mt-0.5 sm:hidden truncate">
                                                        {tx.branch?.name ?? '—'}
                                                    </p>
                                                    {tx.order_time && (
                                                        <p className="text-xs text-muted-foreground">
                                                            {new Date(tx.order_date).toLocaleDateString('en-PH', { month: 'short', day: 'numeric' })}
                                                            {' · '}{tx.order_time.slice(0, 5)}
                                                        </p>
                                                    )}
                                                </div>
                                                <div className="hidden sm:block min-w-0">
                                                    <p className="text-xs truncate">{tx.branch?.name ?? '—'}</p>
                                                    <p className="text-xs text-muted-foreground truncate">{tx.cashier?.name ?? '—'}</p>
                                                </div>
                                                <div className="text-right">
                                                    <p className="text-sm font-semibold tabular-nums">{peso(parseFloat(tx.total_amount))}</p>
                                                    {tx.payment_method && (
                                                        <span className="inline-flex items-center gap-1 text-xs text-muted-foreground mt-0.5">
                                                            {PM_META[tx.payment_method]?.icon}
                                                            {PM_META[tx.payment_method]?.label}
                                                        </span>
                                                    )}
                                                </div>
                                                <div className="flex items-center justify-end gap-1.5">
                                                    <span className={`h-1.5 w-1.5 rounded-full shrink-0 ${meta.dot}`} />
                                                    <span className="text-xs capitalize whitespace-nowrap">{meta.label}</span>
                                                </div>
                                            </button>
                                        );
                                    })}
                                </div>
                            </>
                        )}
                    </div>

                    {/* Stock Alerts (2/5) */}
                    <div className="lg:col-span-2 bg-background rounded-xl border overflow-hidden">
                        <div className="flex items-center justify-between px-5 py-4">
                            <div className="flex items-center gap-2">
                                <AlertTriangle className="h-4 w-4 text-muted-foreground" />
                                <h2 className="text-sm font-semibold">Stock Alerts</h2>
                            </div>
                            {alertCount > 0 && (
                                <span className="text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 rounded-full px-2 py-0.5">
                                    {alertCount}
                                </span>
                            )}
                        </div>
                        <Separator />

                        {low_stock_items.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-14 text-center">
                                <CheckCircle2 className="h-9 w-9 text-emerald-400/50 mb-3" />
                                <p className="text-sm font-medium text-muted-foreground">All levels healthy</p>
                                <p className="text-xs text-muted-foreground/70 mt-1">No restocking needed</p>
                            </div>
                        ) : (
                            <div>
                                {/* Out of Stock section */}
                                {outOfStock.length > 0 && (
                                    <div>
                                        <div className="flex items-center gap-2 px-5 py-2.5 bg-red-50/60 dark:bg-red-950/20 border-b">
                                            <XCircle className="h-3.5 w-3.5 text-red-500 shrink-0" />
                                            <span className="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider">
                                                Out of Stock ({outOfStock.length})
                                            </span>
                                        </div>
                                        <div className="divide-y">
                                            {outOfStock.map((item) => (
                                                <StockRow key={item.id} item={item} critical />
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {/* Low Stock section */}
                                {lowStock.length > 0 && (
                                    <div>
                                        <div className="flex items-center gap-2 px-5 py-2.5 bg-amber-50/60 dark:bg-amber-950/20 border-b">
                                            <AlertTriangle className="h-3.5 w-3.5 text-amber-500 shrink-0" />
                                            <span className="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">
                                                Low Stock ({lowStock.length})
                                            </span>
                                        </div>
                                        <div className="divide-y">
                                            {lowStock.map((item) => (
                                                <StockRow key={item.id} item={item} />
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>

                </div>
            </div>
        </>
    );
}

// ─── Stock Row ────────────────────────────────────────────────────────────────

function StockRow({ item, critical = false }: { item: LowStockItem; critical?: boolean }) {
    const pct = item.reorder_level > 0
        ? Math.min(100, (item.quantity / item.reorder_level) * 100)
        : 0;

    return (
        <div className="px-5 py-3">
            <div className="flex items-start justify-between gap-3 mb-2">
                <div className="min-w-0">
                    <p className="text-sm font-medium leading-snug truncate">{item.product_name}</p>
                    <p className="text-xs font-mono text-muted-foreground mt-0.5">{item.sku}</p>
                </div>
                <div className="text-right shrink-0">
                    <span className={`text-sm font-bold tabular-nums leading-none ${critical ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400'}`}>
                        {item.quantity}
                    </span>
                    <span className="text-xs text-muted-foreground">/{item.reorder_level}</span>
                </div>
            </div>
            <div className="h-1 w-full bg-muted rounded-full overflow-hidden">
                <div
                    className={`h-full rounded-full ${critical ? 'bg-red-500' : pct < 40 ? 'bg-amber-500' : 'bg-amber-300'}`}
                    style={{ width: `${pct}%` }}
                />
            </div>
        </div>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
};

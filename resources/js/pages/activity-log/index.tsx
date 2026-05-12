import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import {
    Search, Filter, ChevronDown, ChevronRight,
    UserCircle, Package, Building2, ShoppingBag,
    Truck, Users, Shield, HelpCircle, RotateCcw,
} from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import type { ActivityLog, ActivityLogIndexProps } from '@/types';

// ─── Route (Wayfinder not available for this route yet, use literal) ──────────

const ROUTE_INDEX = '/activity-logs';

// ─── Maps ─────────────────────────────────────────────────────────────────────

const EVENT_META: Record<string, { label: string; bg: string; text: string; dot: string }> = {
    created:  { label: 'Created',  bg: 'bg-emerald-100 dark:bg-emerald-950', text: 'text-emerald-700 dark:text-emerald-300', dot: 'bg-emerald-500' },
    updated:  { label: 'Updated',  bg: 'bg-blue-100 dark:bg-blue-950',       text: 'text-blue-700 dark:text-blue-300',       dot: 'bg-blue-500'   },
    deleted:  { label: 'Deleted',  bg: 'bg-red-100 dark:bg-red-950',         text: 'text-red-700 dark:text-red-300',         dot: 'bg-red-500'    },
    restored: { label: 'Restored', bg: 'bg-amber-100 dark:bg-amber-950',     text: 'text-amber-700 dark:text-amber-300',     dot: 'bg-amber-500'  },
};

const LOG_ICONS: Record<string, React.ElementType> = {
    users:        Users,
    products:     Package,
    branches:     Building2,
    'sales-orders': ShoppingBag,
    suppliers:    Truck,
    default:      Shield,
};

const LOG_LABELS: Record<string, string> = {
    users:          'Users',
    products:       'Products',
    branches:       'Branches',
    'sales-orders': 'Sales Orders',
    suppliers:      'Suppliers',
    default:        'System',
};

function shortModel(type: string | null): string {
    if (!type) return '—';
    return type.split('\\').pop() ?? type;
}

function relativeTime(iso: string): string {
    const diff = Date.now() - new Date(iso).getTime();
    const m = Math.floor(diff / 60000);
    if (m < 1)  return 'Just now';
    if (m < 60) return `${m}m ago`;
    const h = Math.floor(m / 60);
    if (h < 24) return `${h}h ago`;
    const d = Math.floor(h / 24);
    if (d < 7)  return `${d}d ago`;
    return new Date(iso).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
}

function absTime(iso: string): string {
    return new Date(iso).toLocaleString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

// ─── Sub-components ────────────────────────────────────────────────────────

function EventBadge({ event }: { event: string | null }) {
    const meta = EVENT_META[event ?? ''] ?? {
        label: event ?? 'Unknown',
        bg: 'bg-muted', text: 'text-muted-foreground', dot: 'bg-muted-foreground',
    };
    return (
        <span className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ${meta.bg} ${meta.text}`}>
            <span className={`h-1.5 w-1.5 rounded-full ${meta.dot}`} />
            {meta.label}
        </span>
    );
}

function LogIcon({ logName }: { logName: string | null }) {
    const Icon = LOG_ICONS[logName ?? ''] ?? HelpCircle;
    return <Icon className="h-4 w-4 shrink-0 text-muted-foreground" />;
}

function PropertyDiff({ properties }: { properties: ActivityLog['properties'] }) {
    if (!properties) return null;

    const { attributes, old } = properties;
    if (!attributes) return null;

    const isUpdate = !!old && Object.keys(old).length > 0;

    return (
        <div className="mt-1 space-y-1">
            {Object.entries(attributes).map(([key, newVal]) => {
                const oldVal = old?.[key];
                const changed = isUpdate && JSON.stringify(oldVal) !== JSON.stringify(newVal);
                const added   = isUpdate && !(key in (old ?? {}));
                return (
                    <div key={key} className="flex items-start gap-2 text-xs">
                        <span className="font-mono text-muted-foreground w-28 shrink-0 truncate">{key}</span>
                        {isUpdate && !added ? (
                            <span className="flex items-center gap-1.5 min-w-0">
                                <span className="text-red-500 line-through truncate max-w-[120px]">{String(oldVal ?? '—')}</span>
                                <ChevronRight className="h-3 w-3 text-muted-foreground shrink-0" />
                                <span className={`truncate max-w-[120px] ${changed ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-muted-foreground'}`}>
                                    {String(newVal ?? '—')}
                                </span>
                            </span>
                        ) : (
                            <span className="text-foreground truncate max-w-[200px]">{String(newVal ?? '—')}</span>
                        )}
                    </div>
                );
            })}
        </div>
    );
}

function ActivityRow({ log }: { log: ActivityLog }) {
    const [expanded, setExpanded] = useState(false);
    const hasProps = !!log.properties?.attributes && Object.keys(log.properties.attributes).length > 0;
    const LogIconComp = LOG_ICONS[log.log_name ?? ''] ?? HelpCircle;

    return (
        <>
            <tr
                className={`group border-b transition-colors ${hasProps ? 'cursor-pointer hover:bg-muted/40' : 'hover:bg-muted/20'}`}
                onClick={() => hasProps && setExpanded(e => !e)}
            >
                {/* Time */}
                <td className="px-4 py-3 whitespace-nowrap">
                    <p className="text-sm font-medium" title={absTime(log.created_at)}>
                        {relativeTime(log.created_at)}
                    </p>
                    <p className="text-xs text-muted-foreground mt-0.5">{absTime(log.created_at)}</p>
                </td>

                {/* User (causer) */}
                <td className="px-4 py-3 whitespace-nowrap">
                    <div className="flex items-center gap-2">
                        <div className="h-7 w-7 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <UserCircle className="h-4 w-4 text-primary" />
                        </div>
                        <div className="min-w-0">
                            <p className="text-sm font-medium truncate max-w-[120px]">
                                {log.causer?.name ?? <span className="text-muted-foreground italic">System</span>}
                            </p>
                            {log.causer?.email && (
                                <p className="text-xs text-muted-foreground truncate max-w-[120px]">{log.causer.email}</p>
                            )}
                        </div>
                    </div>
                </td>

                {/* Event */}
                <td className="px-4 py-3 whitespace-nowrap">
                    <EventBadge event={log.event} />
                </td>

                {/* Module */}
                <td className="px-4 py-3 whitespace-nowrap">
                    <div className="flex items-center gap-2">
                        <LogIconComp className="h-4 w-4 shrink-0 text-muted-foreground" />
                        <span className="text-sm capitalize">
                            {LOG_LABELS[log.log_name ?? ''] ?? log.log_name ?? '—'}
                        </span>
                    </div>
                </td>

                {/* Subject */}
                <td className="px-4 py-3 whitespace-nowrap">
                    <p className="text-sm text-muted-foreground">
                        {shortModel(log.subject_type)}
                        {log.subject_id ? <span className="font-mono text-xs ml-1">#{log.subject_id}</span> : ''}
                    </p>
                </td>

                {/* Expand toggle */}
                <td className="px-4 py-3 text-right">
                    {hasProps && (
                        <span className="inline-flex items-center gap-1 text-xs text-muted-foreground group-hover:text-foreground transition-colors">
                            {expanded
                                ? <ChevronDown className="h-3.5 w-3.5" />
                                : <ChevronRight className="h-3.5 w-3.5" />}
                            {Object.keys(log.properties?.attributes ?? {}).length} field{Object.keys(log.properties?.attributes ?? {}).length !== 1 ? 's' : ''}
                        </span>
                    )}
                </td>
            </tr>

            {/* Expanded diff row */}
            {expanded && hasProps && (
                <tr className="bg-muted/30 border-b">
                    <td colSpan={6} className="px-4 py-3 pl-16">
                        <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">
                            {log.event === 'updated' ? 'Changes' : 'Values'}
                        </p>
                        <PropertyDiff properties={log.properties} />
                    </td>
                </tr>
            )}
        </>
    );
}

// ─── Main Page ────────────────────────────────────────────────────────────────

export default function Index({ data, filters, logNames, events }: ActivityLogIndexProps) {
    const [search,    setSearch]    = useState(filters.search    ?? '');
    const [perPage,   setPerPage]   = useState(filters.per_page  ?? 20);
    const [logName,   setLogName]   = useState(filters.log_name  ?? '');
    const [event,     setEvent]     = useState(filters.event     ?? '');
    const [dateFrom,  setDateFrom]  = useState(filters.date_from ?? '');
    const [dateTo,    setDateTo]    = useState(filters.date_to   ?? '');

    const navigate = (params: Record<string, unknown> = {}) =>
        router.get(
            ROUTE_INDEX,
            { search, per_page: perPage, log_name: logName, event, date_from: dateFrom, date_to: dateTo, ...params },
            { preserveState: true, preserveScroll: true },
        );

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        navigate({ search, page: 1 });
    };

    const handleReset = () => {
        setSearch(''); setLogName(''); setEvent(''); setDateFrom(''); setDateTo('');
        router.get(ROUTE_INDEX, {}, { preserveState: false });
    };

    const hasFilters = !!(search || logName || event || dateFrom || dateTo);

    return (
        <>
            <Head title="Activity Log" />
            <div className="flex flex-col gap-5 p-4 lg:p-6">

                {/* Header */}
                <div className="flex items-start justify-between gap-3">
                    <div>
                        <h1 className="text-xl font-semibold tracking-tight">Activity Log</h1>
                        <p className="text-sm text-muted-foreground mt-0.5">
                            {data.total.toLocaleString()} record{data.total !== 1 ? 's' : ''}
                            {data.from && data.to ? ` · showing ${data.from}–${data.to}` : ''}
                        </p>
                    </div>
                    {hasFilters && (
                        <Button variant="ghost" size="sm" className="gap-1.5 text-muted-foreground" onClick={handleReset}>
                            <RotateCcw className="h-3.5 w-3.5" />
                            Clear filters
                        </Button>
                    )}
                </div>

                {/* Filters */}
                <div className="bg-background rounded-xl border p-4">
                    <div className="flex items-center gap-2 mb-3">
                        <Filter className="h-3.5 w-3.5 text-muted-foreground" />
                        <span className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Filters</span>
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-3">
                        {/* Search */}
                        <form onSubmit={handleSearch} className="col-span-1 sm:col-span-2 xl:col-span-2 flex gap-2">
                            <div className="relative flex-1">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
                                <Input
                                    placeholder="Search user, module…"
                                    value={search}
                                    onChange={e => setSearch(e.target.value)}
                                    className="pl-9 h-9 text-sm"
                                />
                            </div>
                            <Button type="submit" size="sm" className="h-9 shrink-0">Search</Button>
                        </form>

                        {/* Module */}
                        <Select value={logName || '__all__'} onValueChange={v => { const val = v === '__all__' ? '' : v; setLogName(val); navigate({ log_name: val, page: 1 }); }}>
                            <SelectTrigger className="h-9 text-sm">
                                <SelectValue placeholder="All modules" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__all__">All modules</SelectItem>
                                {logNames.map(n => (
                                    <SelectItem key={n} value={n}>
                                        {LOG_LABELS[n] ?? n}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        {/* Event */}
                        <Select value={event || '__all__'} onValueChange={v => { const val = v === '__all__' ? '' : v; setEvent(val); navigate({ event: val, page: 1 }); }}>
                            <SelectTrigger className="h-9 text-sm">
                                <SelectValue placeholder="All events" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__all__">All events</SelectItem>
                                {events.map(e => (
                                    <SelectItem key={e} value={e} className="capitalize">{e}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        {/* Date range */}
                        <Input
                            type="date"
                            value={dateFrom}
                            onChange={e => { setDateFrom(e.target.value); navigate({ date_from: e.target.value, page: 1 }); }}
                            className="h-9 text-sm"
                            placeholder="From date"
                        />
                        <Input
                            type="date"
                            value={dateTo}
                            onChange={e => { setDateTo(e.target.value); navigate({ date_to: e.target.value, page: 1 }); }}
                            className="h-9 text-sm"
                            placeholder="To date"
                        />
                    </div>
                </div>

                {/* Table */}
                <div className="bg-background rounded-xl border overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b bg-muted/40">
                                    <th className="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider whitespace-nowrap">Time</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider whitespace-nowrap">User</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider whitespace-nowrap">Event</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider whitespace-nowrap">Module</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider whitespace-nowrap">Subject</th>
                                    <th className="px-4 py-3 text-right text-xs font-semibold text-muted-foreground uppercase tracking-wider whitespace-nowrap">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={6} className="px-4 py-16 text-center">
                                            <Shield className="h-10 w-10 text-muted-foreground/25 mx-auto mb-3" />
                                            <p className="text-sm font-medium text-muted-foreground">No activity found</p>
                                            <p className="text-xs text-muted-foreground/70 mt-1">
                                                {hasFilters ? 'Try adjusting your filters' : 'Activity will appear here as actions are performed'}
                                            </p>
                                        </td>
                                    </tr>
                                ) : (
                                    data.data.map(log => <ActivityRow key={log.id} log={log} />)
                                )}
                            </tbody>
                        </table>
                    </div>

                    {data.last_page > 1 && (
                        <>
                            <Separator />
                            <div className="flex items-center justify-between px-4 py-3 text-sm">
                                <span className="text-muted-foreground">
                                    Page {data.current_page} of {data.last_page} &middot; {data.total.toLocaleString()} records
                                </span>
                                <div className="flex items-center gap-2">
                                    <Button
                                        variant="outline" size="sm"
                                        disabled={data.current_page <= 1}
                                        onClick={() => navigate({ page: data.current_page - 1 })}
                                    >
                                        Previous
                                    </Button>
                                    <Button
                                        variant="outline" size="sm"
                                        disabled={data.current_page >= data.last_page}
                                        onClick={() => navigate({ page: data.current_page + 1 })}
                                    >
                                        Next
                                    </Button>
                                </div>
                            </div>
                        </>
                    )}
                </div>

            </div>
        </>
    );
}

Index.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={[{ title: 'Activity Log', href: ROUTE_INDEX }]}>
        {page}
    </AppLayout>
);

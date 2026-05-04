import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login, register } from '@/routes';
import {
    Package, ArrowRight, TrendingUp, AlertTriangle,
    Activity, CheckCircle, RefreshCw, Building2,
    ShoppingCart, BarChart3, Zap, Bell, Clock,
} from 'lucide-react';
import { Button } from '@/components/ui/button';

function DashboardPreview() {
    return (
        <div className="relative">
            <div className="bg-card border rounded-2xl shadow-2xl overflow-hidden">
                <div className="bg-muted/60 border-b px-4 py-3 flex items-center gap-3">
                    <div className="flex gap-1.5">
                        <div className="w-2.5 h-2.5 rounded-full bg-red-400" />
                        <div className="w-2.5 h-2.5 rounded-full bg-yellow-400" />
                        <div className="w-2.5 h-2.5 rounded-full bg-green-400" />
                    </div>
                    <span className="text-xs text-muted-foreground font-mono">inventorypro · Dashboard</span>
                </div>
                <div className="p-5 space-y-3">
                    <div className="grid grid-cols-2 gap-3">
                        <div className="rounded-xl border bg-muted/20 p-4">
                            <p className="text-xs text-muted-foreground">Today's Revenue</p>
                            <p className="text-2xl font-bold mt-0.5">₱24,830</p>
                            <p className="text-xs text-emerald-500 flex items-center gap-1 mt-1">
                                <TrendingUp className="w-3 h-3" /> +12% vs yesterday
                            </p>
                        </div>
                        <div className="rounded-xl border bg-amber-500/5 border-amber-500/20 p-4">
                            <p className="text-xs text-muted-foreground">Low Stock</p>
                            <p className="text-2xl font-bold mt-0.5 text-amber-500">3 items</p>
                            <p className="text-xs text-muted-foreground mt-1">Requests auto-sent</p>
                        </div>
                        <div className="rounded-xl border bg-muted/20 p-4">
                            <p className="text-xs text-muted-foreground">Branches</p>
                            <p className="text-2xl font-bold mt-0.5">4 / 4</p>
                            <p className="text-xs text-emerald-500 mt-1">All syncing</p>
                        </div>
                        <div className="rounded-xl border bg-muted/20 p-4">
                            <p className="text-xs text-muted-foreground">Gross Profit</p>
                            <p className="text-2xl font-bold mt-0.5">₱9,120</p>
                            <p className="text-xs text-muted-foreground mt-1">36.7% margin</p>
                        </div>
                    </div>
                    <div className="rounded-xl border p-3 flex items-center gap-3 bg-amber-500/5 border-amber-500/20">
                        <div className="w-7 h-7 rounded-lg bg-amber-500/15 flex items-center justify-center flex-shrink-0">
                            <AlertTriangle className="w-3.5 h-3.5 text-amber-500" />
                        </div>
                        <div className="min-w-0 flex-1">
                            <p className="text-xs font-medium truncate">Rice 5kg — Cebu Branch (8 bags left)</p>
                            <p className="text-xs text-muted-foreground">Purchase request auto-created</p>
                        </div>
                        <span className="text-xs bg-amber-500 text-white rounded-md px-2 py-0.5 flex-shrink-0">Review</span>
                    </div>
                </div>
            </div>
            <div className="absolute -top-3 -right-3 bg-emerald-500 text-white text-xs font-semibold px-2.5 py-1 rounded-lg shadow-lg">
                Live
            </div>
            <div className="absolute -bottom-3 -left-3 bg-card border shadow-lg text-xs font-medium px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                <Activity className="w-3 h-3 text-primary" />
                Real-time sync
            </div>
        </div>
    );
}

function POSPreview() {
    return (
        <div className="bg-card border rounded-2xl overflow-hidden shadow-xl">
            <div className="bg-primary/5 border-b px-5 py-3.5 flex items-center gap-2">
                <ShoppingCart className="w-4 h-4 text-primary" />
                <span className="text-sm font-semibold">Point of Sale</span>
                <span className="ml-auto text-xs text-muted-foreground">Cebu Branch — Cashier 1</span>
            </div>
            <div className="p-5 space-y-2.5">
                {[
                    { name: 'White Rice 5kg', qty: 2, price: '₱320.00' },
                    { name: 'Cooking Oil 1L', qty: 1, price: '₱95.00' },
                    { name: 'Sugar 1kg', qty: 3, price: '₱168.00' },
                ].map((item) => (
                    <div key={item.name} className="flex items-center justify-between rounded-lg bg-muted/40 px-4 py-3">
                        <div>
                            <p className="text-sm font-medium">{item.name}</p>
                            <p className="text-xs text-muted-foreground">× {item.qty}</p>
                        </div>
                        <p className="text-sm font-semibold">{item.price}</p>
                    </div>
                ))}
                <div className="border-t pt-3 space-y-1.5">
                    <div className="flex justify-between text-sm text-muted-foreground">
                        <span>Subtotal</span><span>₱583.00</span>
                    </div>
                    <div className="flex justify-between text-sm text-muted-foreground">
                        <span>Tax (12%)</span><span>₱69.96</span>
                    </div>
                    <div className="flex justify-between font-bold text-base pt-1">
                        <span>Total</span><span>₱652.96</span>
                    </div>
                </div>
                <div className="w-full bg-primary text-primary-foreground rounded-xl py-3 text-sm font-semibold text-center mt-1">
                    Confirm Payment — Cash
                </div>
            </div>
        </div>
    );
}

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="InventoryPro — Retail POS & Inventory" />
            <div className="min-h-screen bg-background">

                {/* Nav */}
                <nav className="border-b bg-background/80 backdrop-blur-sm sticky top-0 z-50">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
                        <div className="flex items-center gap-2.5">
                            <div className="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                                <Package className="w-4 h-4 text-primary-foreground" />
                            </div>
                            <span className="text-lg font-bold tracking-tight">InventoryPro</span>
                        </div>
                        <div className="flex items-center gap-3">
                            {auth.user ? (
                                <Link href={dashboard()}>
                                    <Button>Dashboard</Button>
                                </Link>
                            ) : (
                                <>
                                    <Link href={login()}>
                                        <Button variant="ghost" size="sm">Log in</Button>
                                    </Link>
                                    {canRegister && (
                                        <Link href={register()}>
                                            <Button size="sm">Get started</Button>
                                        </Link>
                                    )}
                                </>
                            )}
                        </div>
                    </div>
                </nav>

                {/* Hero */}
                <section className="relative overflow-hidden border-b">
                    <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,hsl(var(--primary)/0.07),transparent_60%)]" />
                    <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
                        <div className="grid lg:grid-cols-2 gap-14 items-center">
                            <div>
                                <div className="inline-flex items-center gap-2 bg-primary/10 text-primary rounded-full px-3.5 py-1 text-sm font-medium mb-5">
                                    <Zap className="w-3.5 h-3.5" />
                                    Built for retail stores
                                </div>
                                <h1 className="text-4xl sm:text-5xl font-bold tracking-tight leading-[1.1] mb-5">
                                    Your shelves stay full.
                                    <br />
                                    <span className="text-primary">Your profits stay clear.</span>
                                </h1>
                                <p className="text-lg text-muted-foreground leading-relaxed mb-8 max-w-lg">
                                    InventoryPro connects your POS, stock levels, and supplier orders into one system — so you spend less time firefighting and more time selling.
                                </p>
                                {auth.user ? (
                                    <Link href={dashboard()}>
                                        <Button size="lg" className="gap-2">
                                            Go to Dashboard <ArrowRight className="w-4 h-4" />
                                        </Button>
                                    </Link>
                                ) : (
                                    <div className="flex flex-col sm:flex-row gap-3">
                                        <Link href={register()}>
                                            <Button size="lg" className="gap-2">
                                                Get started <ArrowRight className="w-4 h-4" />
                                            </Button>
                                        </Link>
                                        <Link href={login()}>
                                            <Button size="lg" variant="outline">Sign in</Button>
                                        </Link>
                                    </div>
                                )}
                            </div>
                            <div className="lg:pl-6">
                                <DashboardPreview />
                            </div>
                        </div>
                    </div>
                </section>

                {/* Pain strip */}
                <section className="py-16 border-b">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <p className="text-center text-xs font-semibold text-muted-foreground mb-8 uppercase tracking-widest">
                            Sound familiar?
                        </p>
                        <div className="grid sm:grid-cols-3 gap-4">
                            {[
                                { icon: AlertTriangle, text: '"We find out we\'re out of stock when the customer is already at the counter."', color: 'text-red-500 bg-red-500/10' },
                                { icon: Clock, text: '"Our manager drives to every branch just to see what\'s left in stock."', color: 'text-amber-500 bg-amber-500/10' },
                                { icon: BarChart3, text: '"We sell a lot, but at end of month we can\'t explain where the money went."', color: 'text-blue-500 bg-blue-500/10' },
                            ].map(({ icon: Icon, text, color }) => (
                                <div key={text} className="flex items-center gap-4 rounded-xl border bg-card p-5">
                                    <div className={`w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 ${color}`}>
                                        <Icon className="w-5 h-5" />
                                    </div>
                                    <p className="text-sm font-medium text-muted-foreground italic">{text}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Core benefits */}
                <section className="py-24 bg-muted/30">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="text-center mb-14">
                            <h2 className="text-3xl font-bold mb-3">What InventoryPro fixes</h2>
                            <p className="text-muted-foreground text-lg max-w-xl mx-auto">
                                Three problems every retail store faces — solved in one platform.
                            </p>
                        </div>
                        <div className="grid md:grid-cols-3 gap-8">
                            {[
                                {
                                    icon: RefreshCw,
                                    color: 'text-primary bg-primary/10',
                                    title: 'Never run out of stock again',
                                    body: 'When inventory drops below your set threshold, purchase requests go out automatically. By the time your team notices, the reorder is already moving.',
                                    tag: 'Auto-reorder',
                                },
                                {
                                    icon: BarChart3,
                                    color: 'text-emerald-500 bg-emerald-500/10',
                                    title: 'Know your real profit on every sale',
                                    body: 'Every item sold is costed to the exact batch it came from. No guesswork — you always know what you paid for each unit and what you made on the sale.',
                                    tag: 'Exact batch costing',
                                },
                                {
                                    icon: Building2,
                                    color: 'text-blue-500 bg-blue-500/10',
                                    title: 'Every branch, one dashboard',
                                    body: 'Stock levels, sales, and pending orders across all your locations update in real time. No more calling each branch or waiting for end-of-day reports.',
                                    tag: 'Multi-branch',
                                },
                            ].map(({ icon: Icon, color, title, body, tag }) => (
                                <div key={title} className="bg-card border rounded-2xl p-8 flex flex-col gap-5 hover:shadow-md transition-shadow">
                                    <div>
                                        <div className={`w-11 h-11 rounded-xl flex items-center justify-center ${color} mb-5`}>
                                            <Icon className="w-5 h-5" />
                                        </div>
                                        <h3 className="text-lg font-bold mb-2">{title}</h3>
                                        <p className="text-muted-foreground text-sm leading-relaxed">{body}</p>
                                    </div>
                                    <span className={`self-start text-xs font-semibold px-2.5 py-1 rounded-full ${color}`}>{tag}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* How it works */}
                <section className="py-24">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="text-center mb-14">
                            <h2 className="text-3xl font-bold mb-3">Simple from day one</h2>
                            <p className="text-muted-foreground text-lg">Three steps and your whole operation is connected.</p>
                        </div>
                        <div className="grid md:grid-cols-3 gap-6">
                            {[
                                {
                                    n: '1',
                                    icon: Package,
                                    title: 'Add your products & branches',
                                    desc: 'Set up your product catalog with reorder thresholds. Assign inventory to each branch and go.',
                                },
                                {
                                    n: '2',
                                    icon: ShoppingCart,
                                    title: 'Sell — stock updates itself',
                                    desc: 'Cashiers ring up sales on the POS. Every transaction deducts from the right batch, right branch, instantly.',
                                },
                                {
                                    n: '3',
                                    icon: Bell,
                                    title: 'Reorders happen automatically',
                                    desc: 'When stock hits your threshold, purchase requests are created and sent to your manager. You review — the grunt work is gone.',
                                },
                            ].map(({ n, icon: Icon, title, desc }) => (
                                <div key={n} className="bg-card border rounded-2xl p-8 relative overflow-hidden">
                                    <div className="absolute top-4 right-5 text-7xl font-black text-muted/30 leading-none select-none">
                                        {n}
                                    </div>
                                    <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center mb-5">
                                        <Icon className="w-5 h-5 text-primary" />
                                    </div>
                                    <h3 className="font-bold mb-2">{title}</h3>
                                    <p className="text-sm text-muted-foreground leading-relaxed">{desc}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* POS spotlight */}
                <section className="py-24 bg-muted/30">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="grid lg:grid-cols-2 gap-14 items-center">
                            <POSPreview />
                            <div>
                                <div className="inline-flex items-center gap-2 bg-primary/10 text-primary rounded-full px-3.5 py-1 text-sm font-medium mb-5">
                                    <Zap className="w-3.5 h-3.5" />
                                    Point of Sale
                                </div>
                                <h2 className="text-3xl font-bold mb-5 leading-tight">
                                    Checkout in seconds,<br />not minutes.
                                </h2>
                                <div className="space-y-4">
                                    {[
                                        'Search or scan products — items added instantly',
                                        'Tax calculated automatically per product',
                                        'Cash handling with exact change shown to cashier',
                                        'Every sale updates stock across all branches in real time',
                                        'Daily sales summary ready at the end of every shift',
                                    ].map((point) => (
                                        <div key={point} className="flex items-start gap-3">
                                            <CheckCircle className="w-5 h-5 text-primary flex-shrink-0 mt-0.5" />
                                            <span className="text-muted-foreground text-sm">{point}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* CTA */}
                <section className="py-28">
                    <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                        <h2 className="text-3xl sm:text-4xl font-bold mb-4">
                            Your store deserves better tools.
                        </h2>
                        <p className="text-muted-foreground text-lg mb-3 max-w-xl mx-auto">
                            Available as a one-time license or a monthly subscription — pick what works for your business.
                        </p>
                        <p className="text-sm text-muted-foreground mb-10">
                            No hidden fees. No per-transaction charges.
                        </p>
                        {!auth.user && (
                            <div className="flex flex-col sm:flex-row gap-3 justify-center">
                                <Link href={register()}>
                                    <Button size="lg" className="gap-2">
                                        Get started <ArrowRight className="w-4 h-4" />
                                    </Button>
                                </Link>
                                <Link href={login()}>
                                    <Button size="lg" variant="outline">Sign in</Button>
                                </Link>
                            </div>
                        )}
                    </div>
                </section>

                {/* Footer */}
                <footer className="border-t py-10">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div className="flex items-center gap-2">
                            <div className="w-6 h-6 bg-primary rounded-md flex items-center justify-center">
                                <Package className="w-3.5 h-3.5 text-primary-foreground" />
                            </div>
                            <span className="font-semibold text-sm">InventoryPro</span>
                        </div>
                        <p className="text-xs text-muted-foreground">© 2026 InventoryPro. All rights reserved.</p>
                    </div>
                </footer>

            </div>
        </>
    );
}

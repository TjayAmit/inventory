import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login, register } from '@/routes';
import { Package, BarChart3, Users, Shield, ArrowRight, CheckCircle, Zap, Warehouse } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Welcome to InventoryPro" />
            <div className="min-h-screen bg-background">
                {/* Navigation */}
                <nav className="border-b bg-card/50 backdrop-blur-sm sticky top-0 z-50">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between items-center h-16">
                            <div className="flex items-center gap-2">
                                <div className="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                                    <Package className="w-5 h-5 text-primary-foreground" />
                                </div>
                                <span className="text-xl font-bold">InventoryPro</span>
                            </div>
                            <div className="flex items-center gap-4">
                                {auth.user ? (
                                    <Link href={dashboard()}>
                                        <Button>Dashboard</Button>
                                    </Link>
                                ) : (
                                    <>
                                        <Link href={login()}>
                                            <Button variant="ghost">Log in</Button>
                                        </Link>
                                        {canRegister && (
                                            <Link href={register()}>
                                                <Button>Get Started</Button>
                                            </Link>
                                        )}
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </nav>

                {/* Hero Section */}
                <section className="relative overflow-hidden">
                    <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-background to-background" />
                    <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
                        <div className="text-center">
                            <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight mb-6">
                                Streamline Your{' '}
                                <span className="text-primary">Inventory Management</span>
                            </h1>
                            <p className="text-xl text-muted-foreground max-w-3xl mx-auto mb-10">
                                Track products, manage stock levels, and optimize your supply chain 
                                with our powerful, intuitive inventory management system.
                            </p>
                            <div className="flex flex-col sm:flex-row gap-4 justify-center">
                                {auth.user ? (
                                    <Link href={dashboard()}>
                                        <Button size="lg" className="gap-2">
                                            Go to Dashboard
                                            <ArrowRight className="w-4 h-4" />
                                        </Button>
                                    </Link>
                                ) : (
                                    <>
                                        <Link href={register()}>
                                            <Button size="lg" className="gap-2">
                                                Start Free Trial
                                                <ArrowRight className="w-4 h-4" />
                                            </Button>
                                        </Link>
                                        <Link href={login()}>
                                            <Button size="lg" variant="outline">
                                                Sign In
                                            </Button>
                                        </Link>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </section>

                {/* Features Section */}
                <section className="py-24 bg-muted/30">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl font-bold mb-4">Everything You Need</h2>
                            <p className="text-muted-foreground text-lg">
                                Comprehensive tools to manage your inventory efficiently
                            </p>
                        </div>
                        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                            <div className="bg-card rounded-xl p-6 shadow-sm border hover:shadow-md transition-shadow">
                                <div className="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4 text-primary">
                                    <Warehouse className="w-6 h-6" />
                                </div>
                                <h3 className="text-lg font-semibold mb-2">Product Management</h3>
                                <p className="text-muted-foreground text-sm">Organize and track all your products with detailed information and categorization.</p>
                            </div>
                            <div className="bg-card rounded-xl p-6 shadow-sm border hover:shadow-md transition-shadow">
                                <div className="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4 text-primary">
                                    <BarChart3 className="w-6 h-6" />
                                </div>
                                <h3 className="text-lg font-semibold mb-2">Real-time Analytics</h3>
                                <p className="text-muted-foreground text-sm">Get insights into stock levels, sales trends, and inventory performance.</p>
                            </div>
                            <div className="bg-card rounded-xl p-6 shadow-sm border hover:shadow-md transition-shadow">
                                <div className="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4 text-primary">
                                    <Users className="w-6 h-6" />
                                </div>
                                <h3 className="text-lg font-semibold mb-2">Team Collaboration</h3>
                                <p className="text-muted-foreground text-sm">Work together with role-based access control and user management.</p>
                            </div>
                            <div className="bg-card rounded-xl p-6 shadow-sm border hover:shadow-md transition-shadow">
                                <div className="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4 text-primary">
                                    <Shield className="w-6 h-6" />
                                </div>
                                <h3 className="text-lg font-semibold mb-2">Secure & Reliable</h3>
                                <p className="text-muted-foreground text-sm">Enterprise-grade security with 99.9% uptime guarantee.</p>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Stats Section */}
                <section className="py-24">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="grid md:grid-cols-3 gap-8 text-center">
                            <div className="p-6">
                                <div className="text-4xl font-bold text-primary mb-2">10K+</div>
                                <div className="text-muted-foreground">Active Users</div>
                            </div>
                            <div className="p-6">
                                <div className="text-4xl font-bold text-primary mb-2">99.9%</div>
                                <div className="text-muted-foreground">Uptime</div>
                            </div>
                            <div className="p-6">
                                <div className="text-4xl font-bold text-primary mb-2">24/7</div>
                                <div className="text-muted-foreground">Support</div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Benefits Section */}
                <section className="py-24 bg-muted/30">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="grid lg:grid-cols-2 gap-12 items-center">
                            <div>
                                <h2 className="text-3xl font-bold mb-6">Why Choose InventoryPro?</h2>
                                <div className="space-y-4">
                                    <div className="flex items-center gap-3">
                                        <CheckCircle className="w-5 h-5 text-primary flex-shrink-0" />
                                        <span>Free 14-day trial, no credit card required</span>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <CheckCircle className="w-5 h-5 text-primary flex-shrink-0" />
                                        <span>Easy setup and intuitive interface</span>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <CheckCircle className="w-5 h-5 text-primary flex-shrink-0" />
                                        <span>Scalable for businesses of any size</span>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <CheckCircle className="w-5 h-5 text-primary flex-shrink-0" />
                                        <span>Comprehensive reporting and analytics</span>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <CheckCircle className="w-5 h-5 text-primary flex-shrink-0" />
                                        <span>Integration with popular e-commerce platforms</span>
                                    </div>
                                </div>
                            </div>
                            <div className="bg-gradient-to-br from-primary/10 to-primary/5 rounded-2xl p-8">
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="bg-card rounded-xl p-6 shadow-sm">
                                        <Zap className="w-8 h-8 text-primary mb-3" />
                                        <div className="text-2xl font-bold">Fast</div>
                                        <div className="text-sm text-muted-foreground">Lightning quick</div>
                                    </div>
                                    <div className="bg-card rounded-xl p-6 shadow-sm">
                                        <CheckCircle className="w-8 h-8 text-green-500 mb-3" />
                                        <div className="text-2xl font-bold">Accurate</div>
                                        <div className="text-sm text-muted-foreground">99.9% precision</div>
                                    </div>
                                    <div className="bg-card rounded-xl p-6 shadow-sm">
                                        <Shield className="w-8 h-8 text-blue-500 mb-3" />
                                        <div className="text-2xl font-bold">Secure</div>
                                        <div className="text-sm text-muted-foreground">Enterprise grade</div>
                                    </div>
                                    <div className="bg-card rounded-xl p-6 shadow-sm">
                                        <Users className="w-8 h-8 text-purple-500 mb-3" />
                                        <div className="text-2xl font-bold">Collaborative</div>
                                        <div className="text-sm text-muted-foreground">Team ready</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* CTA Section */}
                <section className="py-24">
                    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                        <h2 className="text-3xl font-bold mb-4">
                            Ready to Transform Your Inventory Management?
                        </h2>
                        <p className="text-muted-foreground text-lg mb-8">
                            Join thousands of businesses already using InventoryPro to streamline their operations.
                        </p>
                        {!auth.user && (
                            <Link href={register()}>
                                <Button size="lg" className="gap-2">
                                    Start Your Free Trial
                                    <ArrowRight className="w-4 h-4" />
                                </Button>
                            </Link>
                        )}
                    </div>
                </section>

                {/* Footer */}
                <footer className="border-t py-12 bg-muted/30">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex flex-col md:flex-row justify-between items-center gap-4">
                            <div className="flex items-center gap-2">
                                <div className="w-6 h-6 bg-primary rounded flex items-center justify-center">
                                    <Package className="w-4 h-4 text-primary-foreground" />
                                </div>
                                <span className="font-semibold">InventoryPro</span>
                            </div>
                            <p className="text-sm text-muted-foreground">
                                © 2024 InventoryPro. All rights reserved.
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}

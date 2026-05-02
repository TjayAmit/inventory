import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Package, Sparkles, Zap, Lock, CheckCircle } from 'lucide-react';

export default function Register() {
    return (
        <>
            <Head title="Register" />
            <div className="min-h-screen flex">
                {/* Right Side - Branding (shown on right for register) */}
                <div className="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary/90 to-primary flex-col justify-between p-12 text-primary-foreground order-2">
                    <div>
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <Package className="w-6 h-6" />
                            </div>
                            <span className="text-2xl font-bold">InventoryPro</span>
                        </div>
                    </div>

                    <div className="space-y-8">
                        <h1 className="text-4xl font-bold leading-tight">
                            Start Your Free Trial Today
                        </h1>
                        <p className="text-lg opacity-90">
                            Join thousands of businesses that trust InventoryPro to manage their inventory efficiently.
                        </p>

                        <div className="space-y-4">
                            <div className="flex items-center gap-3">
                                <CheckCircle className="w-5 h-5" />
                                <span className="text-sm">Free 14-day trial</span>
                            </div>
                            <div className="flex items-center gap-3">
                                <CheckCircle className="w-5 h-5" />
                                <span className="text-sm">No credit card required</span>
                            </div>
                            <div className="flex items-center gap-3">
                                <CheckCircle className="w-5 h-5" />
                                <span className="text-sm">Cancel anytime</span>
                            </div>
                            <div className="flex items-center gap-3">
                                <CheckCircle className="w-5 h-5" />
                                <span className="text-sm">Full feature access</span>
                            </div>
                        </div>

                        <div className="grid grid-cols-3 gap-4 pt-4">
                            <div className="text-center">
                                <div className="text-3xl font-bold">10K+</div>
                                <div className="text-sm opacity-75">Active Users</div>
                            </div>
                            <div className="text-center">
                                <div className="text-3xl font-bold">99.9%</div>
                                <div className="text-sm opacity-75">Uptime</div>
                            </div>
                            <div className="text-center">
                                <div className="text-3xl font-bold">24/7</div>
                                <div className="text-sm opacity-75">Support</div>
                            </div>
                        </div>
                    </div>

                    <div className="text-sm opacity-75">
                        © 2024 InventoryPro. All rights reserved.
                    </div>
                </div>

                {/* Left Side - Register Form */}
                <div className="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 bg-background order-1">
                    <div className="w-full max-w-md space-y-6">
                        {/* Mobile Logo */}
                        <div className="lg:hidden flex items-center justify-center gap-3 mb-8">
                            <div className="w-10 h-10 bg-primary/20 rounded-lg flex items-center justify-center">
                                <Package className="w-6 h-6 text-primary" />
                            </div>
                            <span className="text-2xl font-bold">InventoryPro</span>
                        </div>

                        <div className="text-center space-y-2">
                            <h2 className="text-2xl font-bold tracking-tight">Create your account</h2>
                            <p className="text-muted-foreground">
                                Get started with your free trial today
                            </p>
                        </div>

                        <Form
                            {...store.form()}
                            resetOnSuccess={['password', 'password_confirmation']}
                            disableWhileProcessing
                            className="space-y-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="space-y-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="name">Full name</Label>
                                            <Input
                                                id="name"
                                                type="text"
                                                name="name"
                                                required
                                                autoFocus
                                                tabIndex={1}
                                                autoComplete="name"
                                                placeholder="John Doe"
                                                className="h-11"
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="email">Email address</Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                name="email"
                                                required
                                                tabIndex={2}
                                                autoComplete="email"
                                                placeholder="name@company.com"
                                                className="h-11"
                                            />
                                            <InputError message={errors.email} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="password">Password</Label>
                                            <PasswordInput
                                                id="password"
                                                name="password"
                                                required
                                                tabIndex={3}
                                                autoComplete="new-password"
                                                placeholder="Create a strong password"
                                                className="h-11"
                                            />
                                            <InputError message={errors.password} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="password_confirmation">Confirm password</Label>
                                            <PasswordInput
                                                id="password_confirmation"
                                                name="password_confirmation"
                                                required
                                                tabIndex={4}
                                                autoComplete="new-password"
                                                placeholder="Confirm your password"
                                                className="h-11"
                                            />
                                            <InputError message={errors.password_confirmation} />
                                        </div>

                                        <Button
                                            type="submit"
                                            className="w-full h-11"
                                            tabIndex={5}
                                            disabled={processing}
                                            data-test="register-user-button"
                                        >
                                            {processing && <Spinner className="mr-2" />}
                                            Create account
                                        </Button>
                                    </div>

                                    <div className="text-center text-sm text-muted-foreground pt-4">
                                        Already have an account?{' '}
                                        <TextLink href={login()} tabIndex={6}>
                                            Sign in
                                        </TextLink>
                                    </div>
                                </>
                            )}
                        </Form>
                    </div>
                </div>
            </div>
        </>
    );
}

Register.layout = null;

import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Package, Shield, Users, BarChart3 } from 'lucide-react';

type Props = {
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
};

export default function Login({
    status,
    canResetPassword,
    canRegister,
}: Props) {
    return (
        <>
            <Head title="Log in" />
            <div className="min-h-screen flex">
                {/* Left Side - Branding */}
                <div className="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary/90 to-primary flex-col justify-between p-12 text-primary-foreground">
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
                            Streamline Your Inventory Management
                        </h1>
                        <p className="text-lg opacity-90">
                            Track products, manage stock levels, and optimize your supply chain with our powerful inventory management system.
                        </p>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="flex items-center gap-3 bg-white/10 rounded-lg p-4">
                                <Shield className="w-5 h-5" />
                                <span className="text-sm font-medium">Secure & Reliable</span>
                            </div>
                            <div className="flex items-center gap-3 bg-white/10 rounded-lg p-4">
                                <Users className="w-5 h-5" />
                                <span className="text-sm font-medium">Team Collaboration</span>
                            </div>
                            <div className="flex items-center gap-3 bg-white/10 rounded-lg p-4">
                                <BarChart3 className="w-5 h-5" />
                                <span className="text-sm font-medium">Analytics & Reports</span>
                            </div>
                            <div className="flex items-center gap-3 bg-white/10 rounded-lg p-4">
                                <Package className="w-5 h-5" />
                                <span className="text-sm font-medium">Real-time Tracking</span>
                            </div>
                        </div>
                    </div>

                    <div className="text-sm opacity-75">
                        © 2024 InventoryPro. All rights reserved.
                    </div>
                </div>

                {/* Right Side - Login Form */}
                <div className="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 bg-background">
                    <div className="w-full max-w-md space-y-6">
                        {/* Mobile Logo */}
                        <div className="lg:hidden flex items-center justify-center gap-3 mb-8">
                            <div className="w-10 h-10 bg-primary/20 rounded-lg flex items-center justify-center">
                                <Package className="w-6 h-6 text-primary" />
                            </div>
                            <span className="text-2xl font-bold">InventoryPro</span>
                        </div>

                        <div className="text-center space-y-2">
                            <h2 className="text-2xl font-bold tracking-tight">Welcome back</h2>
                            <p className="text-muted-foreground">
                                Enter your credentials to access your account
                            </p>
                        </div>

                        <Form
                            {...store.form()}
                            resetOnSuccess={['password']}
                            className="space-y-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="space-y-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="email">Email address</Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                name="email"
                                                required
                                                autoFocus
                                                tabIndex={1}
                                                autoComplete="email"
                                                placeholder="name@company.com"
                                                className="h-11"
                                            />
                                            <InputError message={errors.email} />
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between">
                                                <Label htmlFor="password">Password</Label>
                                                {canResetPassword && (
                                                    <TextLink
                                                        href={request()}
                                                        className="text-sm"
                                                        tabIndex={5}
                                                    >
                                                        Forgot password?
                                                    </TextLink>
                                                )}
                                            </div>
                                            <PasswordInput
                                                id="password"
                                                name="password"
                                                required
                                                tabIndex={2}
                                                autoComplete="current-password"
                                                placeholder="Enter your password"
                                                className="h-11"
                                            />
                                            <InputError message={errors.password} />
                                        </div>

                                        <div className="flex items-center space-x-3">
                                            <Checkbox
                                                id="remember"
                                                name="remember"
                                                tabIndex={3}
                                            />
                                            <Label htmlFor="remember" className="text-sm">Remember me</Label>
                                        </div>

                                        <Button
                                            type="submit"
                                            className="w-full h-11"
                                            tabIndex={4}
                                            disabled={processing}
                                            data-test="login-button"
                                        >
                                            {processing && <Spinner className="mr-2" />}
                                            Sign in
                                        </Button>
                                    </div>

                                    {canRegister && (
                                        <div className="text-center text-sm text-muted-foreground pt-4">
                                            Don't have an account?{' '}
                                            <TextLink href={register()} tabIndex={5}>
                                                Create an account
                                            </TextLink>
                                        </div>
                                    )}
                                </>
                            )}
                        </Form>

                        {status && (
                            <div className="text-center text-sm font-medium text-green-600 bg-green-50 p-3 rounded-md">
                                {status}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

Login.layout = null;

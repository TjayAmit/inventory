import { Link, usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';
import { Package, BarChart3, Store, Shield } from 'lucide-react';

export default function AuthSplitLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    const { name } = usePage().props;

    const features = [
        { icon: Package, text: 'Real-time inventory tracking' },
        { icon: BarChart3, text: 'Sales analytics & insights' },
        { icon: Store, text: 'Multi-store management' },
        { icon: Shield, text: 'Secure cloud storage' },
    ];

    return (
        <div className="relative grid min-h-screen lg:grid-cols-2">
            {/* Left Panel - Branded with Filament Theme */}
            <div className="relative hidden flex-col justify-between bg-[#09090B] p-10 text-white lg:flex">
                {/* Gradient overlay with orange accent */}
                <div className="absolute inset-0 bg-gradient-to-br from-[#E38A00]/10 via-transparent to-[#09090B]" />

                {/* Subtle grid pattern */}
                <div className="absolute inset-0 bg-[linear-gradient(to_right,#27272A_1px,transparent_1px),linear-gradient(to_bottom,#27272A_1px,transparent_1px)] bg-[size:4rem_4rem] opacity-[0.03]" />

                <Link
                    href={home()}
                    className="relative z-20 flex items-center gap-3 text-xl font-semibold tracking-tight"
                >
                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E38A00]/10 ring-1 ring-[#E38A00]/20">
                        <AppLogoIcon className="h-6 w-6 fill-current text-[#E38A00]" />
                    </div>
                    {name}
                </Link>

                <div className="relative z-20">
                    <h2 className="text-3xl font-bold tracking-tight text-white">
                        Streamline Your Retail Operations
                    </h2>
                    <p className="mt-4 text-lg text-neutral-400">
                        The complete inventory management solution for modern retail businesses.
                    </p>

                    <div className="mt-8 space-y-4">
                        {features.map((feature, index) => (
                            <div key={index} className="flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-[#18181B] ring-1 ring-[#27272A]">
                                    <feature.icon className="h-5 w-5 text-[#E38A00]" />
                                </div>
                                <span className="text-sm font-medium text-neutral-300">
                                    {feature.text}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="relative z-20 text-sm text-neutral-500">
                    © {new Date().getFullYear()} {name}. All rights reserved.
                </div>
            </div>

            {/* Right Panel - Form */}
            <div className="flex flex-col justify-center bg-[#09090B] px-6 py-12 lg:px-12">
                <div className="mx-auto w-full max-w-sm">
                    {/* Mobile Logo */}
                    <Link
                        href={home()}
                        className="mb-8 flex items-center justify-center gap-2 lg:hidden"
                    >
                        <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-[#E38A00]/10 ring-1 ring-[#E38A00]/20">
                            <AppLogoIcon className="h-5 w-5 fill-current text-[#E38A00]" />
                        </div>
                        <span className="text-lg font-semibold text-white">{name}</span>
                    </Link>

                    <div className="mb-8">
                        <h1 className="text-2xl font-bold tracking-tight text-white">
                            {title}
                        </h1>
                        <p className="mt-2 text-sm text-neutral-400">
                            {description}
                        </p>
                    </div>

                    {children}
                </div>
            </div>
        </div>
    );
}

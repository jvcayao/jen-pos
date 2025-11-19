import AppLoginLogoIcon from '@/components/app-login-logo-icon';
import { type PropsWithChildren } from 'react';

interface AuthLayoutProps {
    name?: string;
}

export default function AuthSimpleLayout({
    children,
}: PropsWithChildren<AuthLayoutProps>) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
            <div className="w-full max-w-sm">
                <div className="flex flex-col gap-8">
                    <div className="flex flex-col items-center gap-4">
                        <AppLoginLogoIcon className="h-32 w-32" />
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}

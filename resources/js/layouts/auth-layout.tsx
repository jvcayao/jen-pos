import AuthLayoutTemplate from '@/layouts/auth/auth-simple-layout';

interface AuthLayoutProps {
    children: React.ReactNode;
    title?: string;
    description?: string;
}

export default function AuthLayout({
    children,
    ...props
}: AuthLayoutProps) {
    return <AuthLayoutTemplate {...props}>{children}</AuthLayoutTemplate>;
}

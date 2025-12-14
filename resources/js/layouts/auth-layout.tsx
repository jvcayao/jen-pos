import { useFlashMessages } from '@/hooks/use-flash-messages';
import AuthLayoutTemplate from '@/layouts/auth/auth-simple-layout';

interface AuthLayoutProps {
    children: React.ReactNode;
    title?: string;
    description?: string;
}

export default function AuthLayout({ children, ...props }: AuthLayoutProps) {
    useFlashMessages();

    return <AuthLayoutTemplate {...props}>{children}</AuthLayoutTemplate>;
}

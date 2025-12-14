import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { dashboard, logout } from '@/routes';
import { index as categoriesIndex } from '@/routes/categories';
import { index as ordersIndex } from '@/routes/orders';
import { index as productsIndex } from '@/routes/products';
import { edit as editProfile } from '@/routes/profile';
import { index as studentsIndex } from '@/routes/students';
import { index as usersIndex } from '@/routes/users';
import { type User } from '@/types';
import { SharedData } from '@/types/sharedData';
import { Link, router, usePage } from '@inertiajs/react';
import {
    FolderTree,
    GraduationCap,
    LayoutDashboard,
    LogOut,
    Package,
    Receipt,
    Settings,
    Users,
} from 'lucide-react';

interface UserMenuContentProps {
    user: User;
}

export function UserMenuContent({ user }: UserMenuContentProps) {
    const cleanup = useMobileNavigation();
    const { auth } = usePage<SharedData>().props;
    const permissions = auth.permissions || [];

    const hasPermission = (permission: string) =>
        permissions.includes(permission);

    const handleLogout = () => {
        cleanup();
        router.flushAll();
    };

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo user={user} showEmail={true} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem asChild>
                    <Link
                        className="block w-full"
                        href={ordersIndex.url()}
                        as="button"
                        prefetch
                        onClick={cleanup}
                    >
                        <Receipt className="mr-2" />
                        Orders
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                {hasPermission('view-dashboard') && (
                    <DropdownMenuItem asChild>
                        <Link
                            className="block w-full"
                            href={dashboard.url()}
                            as="button"
                            prefetch
                            onClick={cleanup}
                        >
                            <LayoutDashboard className="mr-2" />
                            Dashboard
                        </Link>
                    </DropdownMenuItem>
                )}
                {hasPermission('manage-products') && (
                    <DropdownMenuItem asChild>
                        <Link
                            className="block w-full"
                            href={productsIndex.url()}
                            as="button"
                            prefetch
                            onClick={cleanup}
                        >
                            <Package className="mr-2" />
                            Products
                        </Link>
                    </DropdownMenuItem>
                )}
                {hasPermission('manage-categories') && (
                    <DropdownMenuItem asChild>
                        <Link
                            className="block w-full"
                            href={categoriesIndex.url()}
                            as="button"
                            prefetch
                            onClick={cleanup}
                        >
                            <FolderTree className="mr-2" />
                            Categories
                        </Link>
                    </DropdownMenuItem>
                )}
                {hasPermission('manage-students') && (
                    <DropdownMenuItem asChild>
                        <Link
                            className="block w-full"
                            href={studentsIndex.url()}
                            as="button"
                            prefetch
                            onClick={cleanup}
                        >
                            <GraduationCap className="mr-2" />
                            Students
                        </Link>
                    </DropdownMenuItem>
                )}
                {(hasPermission('manage-store-users') ||
                    hasPermission('manage-users')) && (
                    <DropdownMenuItem asChild>
                        <Link
                            className="block w-full"
                            href={usersIndex.url()}
                            as="button"
                            prefetch
                            onClick={cleanup}
                        >
                            <Users className="mr-2" />
                            Users
                        </Link>
                    </DropdownMenuItem>
                )}
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem asChild>
                    <Link
                        className="block w-full"
                        href={editProfile()}
                        as="button"
                        prefetch
                        onClick={cleanup}
                    >
                        <Settings className="mr-2" />
                        Settings
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
                <Link
                    className="block w-full"
                    href={logout()}
                    as="button"
                    onClick={handleLogout}
                    data-test="logout-button"
                >
                    <LogOut className="mr-2" />
                    Log out
                </Link>
            </DropdownMenuItem>
        </>
    );
}

import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { index as menuIndex } from '@/routes/menu';
import { type NavItem } from '@/types';
import { SharedData } from '@/types/sharedData';
import { Link, router, usePage } from '@inertiajs/react';
import { Building2, RefreshCw } from 'lucide-react';
import AppLogo from './app-logo';

export function AppSidebar() {
    const page = usePage<SharedData>();
    const menuNavItems: NavItem[] = page.props.navigation;
    const { currentStore, canSwitchStore } = page.props;

    const handleSwitchStore = () => {
        router.post('/store/switch');
    };

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link
                                href={
                                    currentStore
                                        ? menuIndex.url({
                                              store: currentStore.slug,
                                          })
                                        : '#'
                                }
                                prefetch
                            >
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>

                {currentStore && (
                    <div className="px-2 py-1">
                        <div className="flex items-center gap-2 rounded-md bg-primary/10 px-3 py-2 text-sm">
                            <Building2 className="h-4 w-4 text-primary" />
                            <div className="flex-1 truncate">
                                <span className="font-medium">
                                    {currentStore.name}
                                </span>
                                <span className="ml-1 text-xs text-muted-foreground">
                                    ({currentStore.code})
                                </span>
                            </div>
                            {canSwitchStore && (
                                <button
                                    onClick={handleSwitchStore}
                                    className="rounded p-1 hover:bg-primary/20"
                                    title="Switch Store"
                                >
                                    <RefreshCw className="h-3 w-3" />
                                </button>
                            )}
                        </div>
                    </div>
                )}
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={menuNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}

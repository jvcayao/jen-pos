import { Auth, NavItem } from '@/types/index';

export interface SharedData {
    name: string;
    auth: Auth;
    sidebarOpen: boolean;
    navigation: NavItem[];
    [key: string]: unknown;
}

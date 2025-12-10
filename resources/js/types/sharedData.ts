import { Auth, NavItem } from '@/types/index';

export interface Quote {
    message: string;
    author: string;
}

export interface SharedData {
    name: string;
    auth: Auth;
    sidebarOpen: boolean;
    navigation: NavItem[];
    quote?: Quote;
    [key: string]: unknown;
}

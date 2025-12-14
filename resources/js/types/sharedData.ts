import { NavItem } from '@/types/index';

export interface Quote {
    message: string;
    author: string;
}

export interface CurrentStore {
    id: number;
    name: string;
    slug: string;
    code: string;
}

export interface Auth {
    user: {
        id: number;
        name: string;
        email: string;
        avatar?: string;
        email_verified_at: string | null;
        two_factor_enabled?: boolean;
        created_at: string;
        updated_at: string;
        [key: string]: unknown;
    } | null;
    permissions: string[];
}

export interface FlashMessage {
    message: string | null;
    type: 'success' | 'error' | 'warning' | 'info' | null;
}

export interface SharedData {
    name: string;
    auth: Auth;
    currentStore: CurrentStore | null;
    canSwitchStore: boolean;
    sidebarOpen: boolean;
    navigation: NavItem[];
    quote?: Quote;
    flash: FlashMessage;
    [key: string]: unknown;
}

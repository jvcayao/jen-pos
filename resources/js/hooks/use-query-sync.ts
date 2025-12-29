import { router } from '@inertiajs/react';
import { useEffect, useState, useCallback, useRef } from 'react';

interface QuerySyncOptions {
    search?: string;
    category?: string;
    status?: string;
}

interface UseQuerySyncReturn {
    search: string;
    setSearch: (value: string) => void;
    category: string;
    setCategory: (value: string) => void;
    status: string;
    setStatus: (value: string) => void;
}

/**
 * Custom hook to sync query parameters with URL
 * Debounces URL updates to prevent excessive navigation
 */
export function useQuerySync(initial: QuerySyncOptions = {}): UseQuerySyncReturn {
    const [search, setSearch] = useState(initial.search ?? '');
    const [category, setCategory] = useState(initial.category ?? '');
    const [status, setStatus] = useState(initial.status ?? '');
    const isFirstRender = useRef(true);

    useEffect(() => {
        // Skip the first render to avoid unnecessary navigation on mount
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timeoutId = setTimeout(() => {
            const params: Record<string, string> = {};
            if (search) params.search = search;
            if (category) params.category = category;
            if (status) params.status = status;

            const queryString = new URLSearchParams(params).toString();
            const baseUrl = window.location.pathname;
            const url = queryString ? `${baseUrl}?${queryString}` : baseUrl;

            router.get(
                url,
                {},
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 300);

        return () => clearTimeout(timeoutId);
    }, [search, category, status]);

    return {
        search,
        setSearch: useCallback((value: string) => setSearch(value), []),
        category,
        setCategory: useCallback((value: string) => setCategory(value), []),
        status,
        setStatus: useCallback((value: string) => setStatus(value), []),
    };
}

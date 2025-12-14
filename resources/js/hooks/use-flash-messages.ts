import type { SharedData } from '@/types/sharedData';
import { router, usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { toast } from 'sonner';

export function useFlashMessages() {
    const { flash } = usePage<SharedData>().props;
    const shownRef = useRef<string | null>(null);

    // Use primitive values as dependencies to avoid object reference issues
    const message = flash?.message;
    const type = flash?.type;

    useEffect(() => {
        if (!message || !type) {
            return;
        }

        // Create unique key to prevent showing same message twice on re-renders
        const messageKey = `${type}:${message}`;
        if (shownRef.current === messageKey) return;
        shownRef.current = messageKey;

        switch (type) {
            case 'success':
                toast.success(message);
                break;
            case 'error':
                toast.error(message);
                break;
            case 'warning':
                toast.warning(message);
                break;
            case 'info':
                toast.info(message);
                break;
            default:
                toast(message);
        }
    }, [message, type]);

    // Reset shownRef on navigation to allow flash messages to show again
    useEffect(() => {
        const removeListener = router.on('navigate', () => {
            shownRef.current = null;
        });

        return () => removeListener();
    }, []);
}

import { useEffect, useRef } from 'react';

export default function AlertDialog({
    open,
    title,
    description,
    confirmLabel = 'Confirm',
    cancelLabel = 'Cancel',
    onConfirm,
    onCancel,
    destructive = false,
    isProcessing = false,
}: {
    open: boolean;
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    onConfirm: () => void;
    onCancel: () => void;
    destructive?: boolean;
    isProcessing?: boolean;
}) {
    const dialogRef = useRef<HTMLDivElement | null>(null);
    const previouslyFocused = useRef<HTMLElement | null>(null);

    useEffect(() => {
        if (!open) return;
        previouslyFocused.current =
            document.activeElement as HTMLElement | null;
        const el = dialogRef.current;
        // Focus the dialog container for keyboard handling
        setTimeout(() => el?.focus(), 0);

        function onKey(e: KeyboardEvent) {
            if (e.key === 'Escape') {
                e.stopPropagation();
                onCancel();
            }
            if (e.key === 'Enter') {
                // avoid submitting forms by accident when an input is focused
                const active = document.activeElement;
                if (
                    active &&
                    (active.tagName === 'INPUT' ||
                        active.tagName === 'TEXTAREA' ||
                        (active as HTMLElement).getAttribute('role') ===
                            'textbox')
                ) {
                    return;
                }
                e.preventDefault();
                onConfirm();
            }
        }

        document.addEventListener('keydown', onKey);
        return () => {
            document.removeEventListener('keydown', onKey);
            // restore focus
            try {
                previouslyFocused.current?.focus();
            } catch (e) {
                void e;
            }
        };
    }, [open, onCancel, onConfirm]);

    if (!open) return null;

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center"
            role="dialog"
            aria-modal="true"
            aria-labelledby="alert-dialog-title"
            aria-describedby="alert-dialog-description"
        >
            <div
                className="absolute inset-0 bg-black/50 transition-opacity"
                onClick={onCancel}
            />

            <div
                ref={dialogRef}
                tabIndex={-1}
                className="animate-dialog-in z-10 w-full max-w-lg scale-95 transform rounded-lg border border-sidebar-border/60 bg-background p-4 opacity-0 shadow-xl transition duration-150 ease-out focus:outline-none dark:border-sidebar-border"
            >
                <h3 id="alert-dialog-title" className="text-lg font-semibold">
                    {title}
                </h3>
                {description && (
                    <p
                        id="alert-dialog-description"
                        className="mt-2 text-sm text-muted-foreground"
                    >
                        {description}
                    </p>
                )}

                <div className="mt-4 flex justify-end gap-2">
                    <button
                        onClick={onCancel}
                        className="h-9 rounded border px-3 text-sm"
                        aria-label={cancelLabel}
                    >
                        {cancelLabel}
                    </button>
                    <button
                        onClick={onConfirm}
                        disabled={isProcessing}
                        className={`h-9 rounded px-3 text-sm ${destructive ? 'bg-destructive text-destructive-foreground' : 'bg-primary text-primary-foreground'}`}
                        aria-label={confirmLabel}
                    >
                        {confirmLabel}
                    </button>
                </div>
            </div>

            <style>{`
        @keyframes dialog-in {
          from { transform: translateY(-6px) scale(.98); opacity: 0 }
          to { transform: translateY(0) scale(1); opacity: 1 }
        }
        .animate-dialog-in { animation: dialog-in 160ms cubic-bezier(.2,.8,.2,1) both }
      `}</style>
        </div>
    );
}

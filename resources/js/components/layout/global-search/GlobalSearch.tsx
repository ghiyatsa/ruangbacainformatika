import * as React from 'react';
import { GlobalSearchTrigger } from './GlobalSearchTrigger';
const GlobalSearchDialog = React.lazy(async () => {
    const module = await import('./GlobalSearchDialog');

    return { default: module.GlobalSearchDialog };
});

export function GlobalSearch() {
    const [open, setOpen] = React.useState(false);

    const openDialog = React.useCallback(() => {
        setOpen(true);
    }, []);

    const closeDialog = React.useCallback(() => {
        setOpen(false);
    }, []);

    React.useEffect(() => {
        const down = (event: KeyboardEvent) => {
            if (event.key === 'k' && (event.metaKey || event.ctrlKey)) {
                event.preventDefault();

                if (open) {
                    closeDialog();

                    return;
                }

                openDialog();
            }
        };

        document.addEventListener('keydown', down);
        window.addEventListener('open-global-search', openDialog);

        return () => {
            document.removeEventListener('keydown', down);
            window.removeEventListener('open-global-search', openDialog);
        };
    }, [closeDialog, open, openDialog]);

    const handleOpenChange = React.useCallback(
        (nextOpen: boolean) => {
            if (nextOpen) {
                openDialog();

                return;
            }

            closeDialog();
        },
        [closeDialog, openDialog],
    );

    return (
        <>
            <GlobalSearchTrigger onClick={openDialog} />

            {open ? (
                <React.Suspense fallback={null}>
                    <GlobalSearchDialog
                        open={open}
                        onOpenChange={handleOpenChange}
                    />
                </React.Suspense>
            ) : null}
        </>
    );
}

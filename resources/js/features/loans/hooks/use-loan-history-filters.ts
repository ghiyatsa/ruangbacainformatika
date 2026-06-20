import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import loansRoute from '@/routes/loans';
import type { LoanFilter } from '@/features/loans/types';

interface UseLoanHistoryFiltersOptions {
    currentFilter: LoanFilter;
    currentSearch: string;
}

export function useLoanHistoryFilters({
    currentFilter,
    currentSearch,
}: UseLoanHistoryFiltersOptions) {
    const [searchQuery, setSearchQuery] = useState(currentSearch);

    const applyFilters = (
        nextFilter: LoanFilter,
        nextSearch: string,
    ): void => {
        router.get(
            loansRoute.history.url(),
            {
                filter: nextFilter === 'all' ? '' : nextFilter,
                search: nextSearch.trim(),
            },
            {
                preserveScroll: true,
                preserveState: false,
                replace: true,
            },
        );
    };

    // Debounced search: when the user types, wait 300ms before navigating.
    useEffect(() => {
        const normalizedSearch = searchQuery.trim();

        if (normalizedSearch === currentSearch) {
            return;
        }

        const timeout = window.setTimeout(() => {
            applyFilters(currentFilter, normalizedSearch);
        }, 300);

        return () => window.clearTimeout(timeout);
    }, [currentFilter, currentSearch, searchQuery]);

    return { searchQuery, setSearchQuery, applyFilters };
}

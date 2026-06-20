/**
 * Read the CSRF token published by Laravel into the document head.
 *
 * The token is consumed when issuing manual `fetch` requests outside Inertia's
 * own transport (for example when polling a status endpoint). Returns `null`
 * when the meta tag is missing.
 */
export function getCsrfToken(): string | null {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? null
    );
}

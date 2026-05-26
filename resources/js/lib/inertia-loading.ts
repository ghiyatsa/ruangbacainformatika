export function instantLoadingPageProps() {
    return (
        _currentProps: Record<string, unknown>,
        sharedProps: Record<string, unknown>,
    ) => ({
        ...sharedProps,
        loading: true,
    });
}

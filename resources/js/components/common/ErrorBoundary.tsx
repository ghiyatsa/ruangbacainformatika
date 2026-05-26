import React, { Component, Suspense, lazy } from 'react';
import type { ErrorInfo, ReactNode } from 'react';

const ErrorPage = lazy(() => import('@/pages/error-page'));

interface Props {
    children?: ReactNode;
}

interface State {
    hasError: boolean;
    error: Error | null;
}

export class ErrorBoundary extends Component<Props, State> {
    public state: State = {
        hasError: false,
        error: null,
    };

    public static getDerivedStateFromError(error: Error): State {
        return { hasError: true, error };
    }

    public componentDidCatch(error: Error, errorInfo: ErrorInfo) {
        console.error('Uncaught error:', error, errorInfo);
    }

    public render() {
        if (this.state.hasError) {
            return (
                <Suspense
                    fallback={
                        <main className="flex min-h-screen items-center justify-center bg-background px-6 py-10 text-foreground">
                            <div className="rounded-2xl border border-border/60 bg-card/85 px-6 py-5 text-center shadow-sm">
                                <p className="text-sm font-medium text-muted-foreground">
                                    Memuat halaman error...
                                </p>
                            </div>
                        </main>
                    }
                >
                    <ErrorPage status={500} />
                </Suspense>
            );
        }

        return this.props.children;
    }
}

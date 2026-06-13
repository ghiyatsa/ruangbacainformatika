import React, { Component, Suspense, lazy } from 'react';
import type { ErrorInfo, ReactNode } from 'react';

const ErrorPage = lazy(() => import('@/pages/error/index'));

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
                <Suspense fallback={null}>
                    <ErrorPage status={500} />
                </Suspense>
            );
        }

        return this.props.children;
    }
}

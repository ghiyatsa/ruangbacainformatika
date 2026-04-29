import type { ErrorInfo, ReactNode } from 'react';
import React, { Component } from 'react';
import AlertError from './AlertError';

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
                <div className="flex min-h-[400px] w-full items-center justify-center p-6">
                    <div className="w-full max-w-md">
                        <AlertError
                            title="Terjadi Kesalahan"
                            errors={['Terjadi kesalahan pada aplikasi.']}
                        />
                        <p className="mt-4 text-center text-sm text-muted-foreground">
                            Silakan muat ulang halaman atau hubungi
                            administrator jika masalah berlanjut.
                        </p>
                    </div>
                </div>
            );
        }

        return this.props.children;
    }
}

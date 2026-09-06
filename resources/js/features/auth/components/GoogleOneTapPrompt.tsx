import { router, usePage } from '@inertiajs/react';
import { useEffect, useEffectEvent, useRef } from 'react';
import type { Auth, GoogleAuth } from '@/types';

declare global {
    interface Window {
        google?: {
            accounts?: {
                id?: {
                    initialize: (options: {
                        client_id: string;
                        callback: (response: { credential: string }) => void;
                        auto_select?: boolean;
                        cancel_on_tap_outside?: boolean;
                        context?: 'signin' | 'signup' | 'use';
                        use_fedcm_for_prompt?: boolean;
                    }) => void;
                    prompt: () => void;
                    cancel: () => void;
                };
            };
        };
    }
}

type SharedPageProps = {
    auth: Auth;
    googleAuth: GoogleAuth;
};

const SCRIPT_ID = 'google-identity-services';
let initializedPromptKey: string | null = null;
let attemptedPromptKey: string | null = null;

function shouldDisableForComponent(component: string) {
    return (
        component === 'error/index' ||
        component.startsWith('kiosk/') ||
        (component !== 'welcome/index' && !component.startsWith('auth/'))
    );
}

export default function GoogleOneTapPrompt({
    linkToken,
    disabled = false,
}: {
    linkToken?: string;
    disabled?: boolean;
}) {
    const pageProps = usePage<SharedPageProps>().props;
    const auth = pageProps.auth;
    const googleAuth = pageProps.googleAuth;
    const { component } = usePage();
    const initializedRef = useRef(false);

    const hasAuthProps = auth != null && googleAuth != null;
    const currentUser = auth?.user ?? null;
    const googleClientId = googleAuth?.clientId ?? null;
    const isGoogleEnabled = googleAuth?.enabled ?? false;
    const isOneTapEnabled = googleAuth?.oneTapEnabled ?? false;

    const handleCredential = useEffectEvent(
        (response: { credential: string }) => {
            if (!googleAuth?.oneTapUrl) {
                return;
            }

            router.post(
                googleAuth.oneTapUrl,
                {
                    credential: response.credential,
                    link_token: linkToken,
                },
                {
                    preserveScroll: true,
                    preserveState: true,
                },
            );
        },
    );

    useEffect(() => {
        if (!hasAuthProps) {
            return;
        }

        const clientId = googleClientId;
        const promptKey = `${clientId ?? ''}:${linkToken ?? 'default'}`;

        if (
            disabled ||
            currentUser !== null ||
            !isGoogleEnabled ||
            !isOneTapEnabled ||
            !clientId ||
            shouldDisableForComponent(component)
        ) {
            return;
        }

        let cancelled = false;

        const initializePrompt = () => {
            if (
                cancelled ||
                initializedRef.current ||
                !window.google?.accounts?.id
            ) {
                return;
            }

            if (initializedPromptKey !== promptKey) {
                window.google.accounts.id.initialize({
                    client_id: clientId,
                    callback: handleCredential,
                    auto_select: false,
                    cancel_on_tap_outside: false,
                    context: 'signin',
                    use_fedcm_for_prompt: false,
                });

                initializedPromptKey = promptKey;
            }

            if (attemptedPromptKey === promptKey) {
                initializedRef.current = true;

                return;
            }

            window.google.accounts.id.prompt();
            attemptedPromptKey = promptKey;
            initializedRef.current = true;
        };

        if (window.google?.accounts?.id) {
            initializePrompt();
        } else {
            const existingScript = document.getElementById(
                SCRIPT_ID,
            ) as HTMLScriptElement | null;

            const script =
                existingScript ??
                Object.assign(document.createElement('script'), {
                    id: SCRIPT_ID,
                    src: 'https://accounts.google.com/gsi/client',
                    async: true,
                    defer: true,
                });

            if (!existingScript) {
                document.head.appendChild(script);
            }

            script.addEventListener('load', initializePrompt);

            return () => {
                cancelled = true;
                script.removeEventListener('load', initializePrompt);
                window.google?.accounts?.id?.cancel();
                initializedRef.current = false;
            };
        }

        return () => {
            cancelled = true;
            window.google?.accounts?.id?.cancel();
            initializedRef.current = false;
        };
    }, [
        hasAuthProps,
        currentUser,
        linkToken,
        component,
        disabled,
        googleClientId,
        isGoogleEnabled,
        isOneTapEnabled,
    ]);

    return null;
}

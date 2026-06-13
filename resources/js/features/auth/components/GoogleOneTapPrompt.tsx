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
    const { auth, googleAuth } = usePage<SharedPageProps>().props;
    const { component } = usePage();
    const initializedRef = useRef(false);

    const handleCredential = useEffectEvent(
        (response: { credential: string }) => {
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
        const clientId = googleAuth.clientId;
        const promptKey = `${clientId ?? ''}:${linkToken ?? 'default'}`;

        if (
            disabled ||
            auth.user !== null ||
            !googleAuth.enabled ||
            !googleAuth.oneTapEnabled ||
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
        auth.user,
        linkToken,
        component,
        disabled,
        googleAuth.clientId,
        googleAuth.enabled,
        googleAuth.oneTapEnabled,
    ]);

    return null;
}

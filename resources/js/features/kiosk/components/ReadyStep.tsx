import { router } from '@inertiajs/react';
import { BookMarked, BookUp, ClipboardList, UserPlus } from 'lucide-react';
import { useCallback, useEffect } from 'react';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { KioskShortcutsHelp } from '@/features/kiosk/components/KioskAttractCards';
import { KioskAttractScreen } from '@/features/kiosk/components/KioskAttractScreen';
import { MenuGrid } from '@/features/kiosk/components/MenuGrid';
import { BorrowForm } from '@/features/kiosk/forms/BorrowForm';
import { MemberForm } from '@/features/kiosk/forms/MemberForm';
import { ReturnForm } from '@/features/kiosk/forms/ReturnForm';
import { VisitForm } from '@/features/kiosk/forms/VisitForm';
import { kioskMenuItems } from '@/features/kiosk/menu';
import { useIdleReset } from '@/hooks/use-idle-reset';
import type { LucideIcon } from 'lucide-react';
import type { KioskMenu, KioskProps } from '@/features/kiosk/types';

const MENU_ICONS: Record<Exclude<KioskMenu, 'landing'>, LucideIcon> = {
    visit: ClipboardList,
    member: UserPlus,
    borrow: BookMarked,
    return: BookUp,
};

const SHORTCUT_MENU: Record<string, KioskMenu> = {
    '1': 'visit',
    '2': 'member',
    '3': 'borrow',
    '4': 'return',
};

export function ReadyStep(props: KioskProps) {
    const selectedMenu =
        props.activeMenu === 'landing' ? null : props.activeMenu;
    const activeItem = kioskMenuItems.find((item) => item.key === selectedMenu);
    const ActiveIcon = selectedMenu ? MENU_ICONS[selectedMenu] : null;

    const handleSelect = useCallback(
        (menu: KioskMenu) => {
            if (menu === props.activeMenu) {
                return;
            }

            if (document.activeElement instanceof HTMLElement) {
                document.activeElement.blur();
            }

            router.visit(KioskController.show({ query: { menu } }), {
                only: ['activeMenu', 'memberRegistrationClaim', 'kioskInfo'],
                preserveScroll: true,
                preserveState: true,
            });
        },
        [props.activeMenu],
    );

    useEffect(() => {
        const handleKeyDown = (event: KeyboardEvent) => {
            const activeElement = document.activeElement;
            const isTyping =
                activeElement instanceof HTMLInputElement ||
                activeElement instanceof HTMLTextAreaElement ||
                activeElement instanceof HTMLSelectElement ||
                (activeElement as HTMLElement | null)?.isContentEditable;

            if (event.key === 'Escape') {
                if (isTyping && activeElement instanceof HTMLElement) {
                    event.preventDefault();
                    activeElement.blur();

                    return;
                }

                if (selectedMenu) {
                    event.preventDefault();
                    handleSelect('landing');
                }

                return;
            }

            const isMenuKey = event.key in SHORTCUT_MENU;

            if (!isMenuKey) {
                return;
            }

            const isAltShortcut = event.altKey;
            const isPlainShortcut =
                !event.altKey && !event.ctrlKey && !event.metaKey;

            if (!isAltShortcut && !(isPlainShortcut && !isTyping)) {
                return;
            }

            event.preventDefault();

            if (activeElement instanceof HTMLElement) {
                activeElement.blur();
            }

            handleSelect(SHORTCUT_MENU[event.key]);
        };

        window.addEventListener('keydown', handleKeyDown);

        return () => {
            window.removeEventListener('keydown', handleKeyDown);
        };
    }, [handleSelect, selectedMenu]);

    const returnToLanding = useCallback(() => {
        handleSelect('landing');
    }, [handleSelect]);

    useIdleReset({
        timeoutMs: 90_000,
        enabled: Boolean(selectedMenu),
        onIdle: returnToLanding,
    });

    return (
        <div className="flex h-[calc(100dvh-1rem)] sm:h-[calc(100dvh-1.5rem)] lg:h-[calc(100dvh-2rem)] min-h-0 w-full flex-col justify-center gap-4">
            <div className="grid min-h-0 w-full flex-1 gap-4 lg:grid-cols-[360px_minmax(0,1fr)] xl:grid-cols-[400px_minmax(0,1fr)]">
                <Card className="flex min-h-0 flex-col border-border/70 shadow-sm">
                    <CardContent className="flex min-h-0 flex-1 flex-col justify-between overflow-hidden p-5 sm:p-6">
                        <MenuGrid
                            activeMenu={selectedMenu}
                            onSelect={handleSelect}
                        />

                        <div className="pt-4">
                            <KioskShortcutsHelp />
                        </div>
                    </CardContent>
                </Card>

                <Card className="flex min-h-0 flex-1 flex-col border-border/70 shadow-sm">
                    {selectedMenu ? (
                        <CardHeader className="p-5 pb-0 sm:p-6 sm:pb-0">
                            <div className="flex items-center gap-3">
                                {ActiveIcon ? (
                                    <div className="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                        <ActiveIcon className="size-4" />
                                    </div>
                                ) : null}
                                <div>
                                    <CardTitle>{activeItem?.label}</CardTitle>
                                    <CardDescription>
                                        {activeItem?.helper}
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                    ) : null}

                    <CardContent className="min-h-0 flex-1 overflow-x-hidden overflow-y-auto p-5 sm:p-6">
                        {selectedMenu === 'visit' ? (
                            <VisitForm
                                visitorTypeOptions={props.visitorTypeOptions}
                                purposeOptions={props.purposeOptions}
                            />
                        ) : null}
                        {selectedMenu === 'member' ? (
                            <MemberForm
                                memberRegistrationClaim={
                                    props.memberRegistrationClaim
                                }
                            />
                        ) : null}
                        {selectedMenu === 'borrow' ? (
                            <BorrowForm loanMaxBooks={props.loanMaxBooks} />
                        ) : null}
                        {selectedMenu === 'return' ? <ReturnForm /> : null}
                        {!selectedMenu ? (
                            <KioskAttractScreen
                                session={props.kioskSession}
                                info={props.kioskInfo}
                            />
                        ) : null}
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}

import { usePage } from '@inertiajs/react';
import { BookMarked, BookUp, ClipboardList, UserPlus } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useEffect, useState } from 'react';
import { FlashMessage } from '@/features/kiosk/components/FlashMessage';
import { KioskMenuModal } from '@/features/kiosk/components/KioskMenuModal';
import { MenuGrid } from '@/features/kiosk/components/MenuGrid';
import { BorrowForm } from '@/features/kiosk/forms/BorrowForm';
import { MemberForm } from '@/features/kiosk/forms/MemberForm';
import { ReturnForm } from '@/features/kiosk/forms/ReturnForm';
import { VisitForm } from '@/features/kiosk/forms/VisitForm';
import { kioskMenuItems } from '@/features/kiosk/menu';
import type { FlashProps, KioskMenu, KioskProps } from '@/features/kiosk/types';

const MENU_ICONS: Record<string, LucideIcon> = {
    visit: ClipboardList,
    member: UserPlus,
    borrow: BookMarked,
    return: BookUp,
};

export function ReadyStep(props: KioskProps) {
    const [activeMenu, setActiveMenu] = useState<KioskMenu | null>(null);
    const { props: pageProps } = usePage<FlashProps>();
    const flashSuccess = pageProps.flash?.success;
    const [flashVisible, setFlashVisible] = useState(Boolean(flashSuccess));
    const [prevFlash, setPrevFlash] = useState(flashSuccess);

    if (flashSuccess !== prevFlash) {
        setPrevFlash(flashSuccess);

        if (flashSuccess) {
            setFlashVisible(true);
            setActiveMenu(null);
        }
    }

    useEffect(() => {
        if (!flashVisible || !flashSuccess) {
            return;
        }

        const timer = window.setTimeout(() => setFlashVisible(false), 5000);

        return () => window.clearTimeout(timer);
    }, [flashVisible, flashSuccess]);

    const activeItem = kioskMenuItems.find((item) => item.key === activeMenu);
    const ActiveIcon = activeMenu ? MENU_ICONS[activeMenu] : null;

    const closeModal = () => setActiveMenu(null);

    return (
        <div className="flex w-full max-w-[1500px] flex-col justify-center gap-8">
            {flashVisible ? <FlashMessage message={flashSuccess} /> : null}

            <div className="relative overflow-hidden rounded-[2rem] border bg-linear-to-br from-background via-muted/30 to-background shadow-sm">
                <div className="absolute inset-0 bg-linear-to-r from-primary/5 via-transparent to-primary/5" />

                <div className="relative px-10 pt-10 pb-9 xl:px-12 xl:pt-12 xl:pb-10">
                    <h1 className="mb-3 text-4xl font-bold tracking-tight xl:text-5xl">
                        {props.pageTitle}
                    </h1>
                    <p className="max-w-4xl text-base leading-7 text-muted-foreground xl:text-lg">
                        {props.pageSubtitle}
                    </p>
                </div>
            </div>

            <MenuGrid onSelect={setActiveMenu} />

            <KioskMenuModal
                open={activeMenu !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        closeModal();
                    }
                }}
                menuKey={activeMenu ?? 'visit'}
                icon={ActiveIcon ?? ClipboardList}
                title={activeItem?.label ?? props.pageTitle}
                description={activeItem?.description}
            >
                {activeMenu === 'visit' && (
                    <VisitForm
                        visitorTypeOptions={props.visitorTypeOptions}
                        purposeOptions={props.purposeOptions}
                    />
                )}
                {activeMenu === 'member' && <MemberForm />}
                {activeMenu === 'borrow' && (
                    <BorrowForm loanMaxBooks={props.loanMaxBooks} />
                )}
                {activeMenu === 'return' && (
                    <ReturnForm loanMaxBooks={props.loanMaxBooks} />
                )}
            </KioskMenuModal>
        </div>
    );
}

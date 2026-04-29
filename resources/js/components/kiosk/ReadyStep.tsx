import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { FlashMessage } from '@/components/kiosk/FlashMessage';
import { KioskPanel } from '@/components/kiosk/KioskPanel';
import { MenuGrid } from '@/components/kiosk/MenuGrid';
import { BorrowForm } from '@/components/kiosk/forms/BorrowForm';
import { MemberForm } from '@/components/kiosk/forms/MemberForm';
import { ReturnForm } from '@/components/kiosk/forms/ReturnForm';
import { VisitForm } from '@/components/kiosk/forms/VisitForm';
import { kioskMenuItems } from '@/pages/kiosk/menu';
import type { FlashProps, KioskMenu, KioskProps } from '@/pages/kiosk/types';

export function ReadyStep(props: KioskProps) {
    const [activeMenu, setActiveMenu] = useState<KioskMenu>(
        props.activeMenu ?? 'landing',
    );
    const { props: pageProps } = usePage<FlashProps>();
    const flashSuccess = pageProps.flash?.success;
    const [flashVisible, setFlashVisible] = useState(Boolean(flashSuccess));
    const activeItem = kioskMenuItems.find((item) => item.key === activeMenu);

    useEffect(() => {
        if (!flashSuccess) {
            return;
        }

        setFlashVisible(true);

        const timer = window.setTimeout(() => setFlashVisible(false), 5000);

        return () => window.clearTimeout(timer);
    }, [flashSuccess]);

    return (
        <div className="flex w-full max-w-5xl flex-col gap-6">
            {flashVisible ? <FlashMessage message={flashSuccess} /> : null}

            {activeMenu === 'landing' ? (
                <>
                    <div className="mx-auto max-w-2xl text-center">
                        <p className="text-sm text-muted-foreground">
                            {props.siteName}
                        </p>
                        <h1 className="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">
                            {props.pageTitle}
                        </h1>
                        <p className="mt-3 text-muted-foreground">
                            {props.pageSubtitle}
                        </p>
                    </div>
                    <MenuGrid onSelect={setActiveMenu} />
                </>
            ) : (
                <KioskPanel
                    className="mx-auto max-w-3xl"
                    title={activeItem?.label ?? props.pageTitle}
                    description={activeItem?.description ?? props.pageSubtitle}
                >
                    {activeMenu === 'visit' ? (
                        <VisitForm
                            visitorTypeOptions={props.visitorTypeOptions}
                            purposeOptions={props.purposeOptions}
                            onBack={() => setActiveMenu('landing')}
                        />
                    ) : null}
                    {activeMenu === 'member' ? (
                        <MemberForm onBack={() => setActiveMenu('landing')} />
                    ) : null}
                    {activeMenu === 'borrow' ? (
                        <BorrowForm
                            loanMaxBooks={props.loanMaxBooks}
                            onBack={() => setActiveMenu('landing')}
                        />
                    ) : null}
                    {activeMenu === 'return' ? (
                        <ReturnForm onBack={() => setActiveMenu('landing')} />
                    ) : null}
                </KioskPanel>
            )}
        </div>
    );
}

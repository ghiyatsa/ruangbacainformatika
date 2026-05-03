import { usePage } from '@inertiajs/react';
import { BookMarked, BookUp, ClipboardList, Library, Sparkles, UserPlus } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useEffect, useState } from 'react';
import { FlashMessage } from '@/components/kiosk/FlashMessage';
import { KioskPanel } from '@/components/kiosk/KioskPanel';
import { MenuGrid } from '@/components/kiosk/MenuGrid';
import { BorrowForm } from '@/components/kiosk/forms/BorrowForm';
import { MemberForm } from '@/components/kiosk/forms/MemberForm';
import { ReturnForm } from '@/components/kiosk/forms/ReturnForm';
import { VisitForm } from '@/components/kiosk/forms/VisitForm';
import { Badge } from '@/components/ui/badge';
import { kioskMenuItems } from '@/pages/kiosk/menu';
import type { FlashProps, KioskMenu, KioskProps } from '@/pages/kiosk/types';

const MENU_ICONS: Record<string, LucideIcon> = {
    visit: ClipboardList,
    member: UserPlus,
    borrow: BookMarked,
    return: BookUp,
};

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

    const PanelIcon = activeMenu !== 'landing' ? MENU_ICONS[activeMenu] : null;

    return (
        <div className="flex w-full max-w-5xl flex-col gap-6">
            {flashVisible ? <FlashMessage message={flashSuccess} /> : null}

            {activeMenu === 'landing' ? (
                <>
                    {/* Hero header – matches catalog/welcome style */}
                    <div className="relative overflow-hidden rounded-2xl border bg-gradient-to-br from-background via-muted/30 to-background shadow-sm">
                        <div className="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-primary/5" />

                        <div className="relative px-8 pt-8 pb-7">
                            {/* Icon + badge */}
                            <div className="mb-4 flex items-center gap-2.5">
                                <div className="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Library className="size-4" />
                                </div>
                                <Badge variant="secondary" className="gap-1.5">
                                    <Sparkles data-icon="inline-start" />
                                    {props.siteName}
                                </Badge>
                            </div>

                            <h1 className="mb-2 text-3xl font-bold tracking-tight sm:text-4xl">
                                {props.pageTitle}
                            </h1>
                            <p className="max-w-lg text-muted-foreground">
                                {props.pageSubtitle}
                            </p>
                        </div>
                    </div>

                    <MenuGrid onSelect={setActiveMenu} />
                </>
            ) : (
                <KioskPanel
                    className="mx-auto max-w-3xl"
                    title={activeItem?.label ?? props.pageTitle}
                    description={activeItem?.description ?? props.pageSubtitle}
                    icon={PanelIcon ? <PanelIcon className="size-4" /> : undefined}
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

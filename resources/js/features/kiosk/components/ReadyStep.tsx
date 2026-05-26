import { router } from '@inertiajs/react';
import { BookMarked, BookUp, ClipboardList, UserPlus } from 'lucide-react';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { MenuGrid } from '@/features/kiosk/components/MenuGrid';
import { BorrowForm } from '@/features/kiosk/forms/BorrowForm';
import { MemberForm } from '@/features/kiosk/forms/MemberForm';
import { ReturnForm } from '@/features/kiosk/forms/ReturnForm';
import { VisitForm } from '@/features/kiosk/forms/VisitForm';
import { kioskMenuItems } from '@/features/kiosk/menu';
import type { LucideIcon } from 'lucide-react';
import type { KioskMenu, KioskProps } from '@/features/kiosk/types';

const MENU_ICONS: Record<Exclude<KioskMenu, 'landing'>, LucideIcon> = {
    visit: ClipboardList,
    member: UserPlus,
    borrow: BookMarked,
    return: BookUp,
};

export function ReadyStep(props: KioskProps) {
    const selectedMenu =
        props.activeMenu === 'landing' ? null : props.activeMenu;
    const activeItem = kioskMenuItems.find((item) => item.key === selectedMenu);
    const ActiveIcon = selectedMenu ? MENU_ICONS[selectedMenu] : null;

    const handleSelect = (menu: KioskMenu) => {
        if (menu === props.activeMenu) {
            return;
        }

        router.visit(KioskController.show({ query: { menu } }), {
            only: ['activeMenu', 'pageSubtitle'],
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <div className="flex h-[calc(100dvh-2rem)] min-h-0 w-full flex-col gap-4 py-10">
            <div className="grid min-h-0 flex-1 gap-4 lg:grid-cols-[280px_minmax(0,1fr)]">
                <Card className="flex min-h-0 flex-col border-border/70">
                    <CardContent className="min-h-0 flex-1 overflow-x-hidden overflow-y-auto">
                        <MenuGrid
                            activeMenu={selectedMenu}
                            onSelect={handleSelect}
                        />
                    </CardContent>
                </Card>

                <Card className="flex min-h-0 flex-col border-border/70">
                    <CardHeader>
                        <div className="flex items-center gap-3">
                            {ActiveIcon ? (
                                <div className="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <ActiveIcon className="size-4" />
                                </div>
                            ) : null}
                            <div>
                                <CardTitle>
                                    {activeItem?.label ?? 'Pilih layanan'}
                                </CardTitle>
                                <CardDescription>
                                    {activeItem?.helper ??
                                        'Pilih salah satu menu di samping untuk mulai.'}
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent className="min-h-0 flex-1 overflow-x-hidden overflow-y-auto">
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
                        {selectedMenu === 'return' ? (
                            <ReturnForm loanMaxBooks={props.loanMaxBooks} />
                        ) : null}
                        {!selectedMenu ? (
                            <div className="flex h-full min-h-56 items-center justify-center rounded-2xl border border-dashed border-border/70 bg-muted/20 px-6 text-center">
                                <p className="text-sm text-muted-foreground">
                                    Pilih layanan di sebelah kiri untuk mulai.
                                </p>
                            </div>
                        ) : null}
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}

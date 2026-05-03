import { Head } from '@inertiajs/react';
import { Palette } from 'lucide-react';
import AppearanceTabs from '@/components/settings/AppearanceTabs';
import settings from '@/routes/settings';

export default function Appearance() {
    return (
        <>
            <Head title="Pengaturan tampilan" />
            <h1 className="sr-only">Pengaturan tampilan</h1>

            <div className="flex flex-col gap-10">
                <section className="flex flex-col gap-6">
                    <div className="flex items-start gap-3">
                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <Palette className="h-4 w-4" />
                        </div>
                        <div>
                            <h2 className="text-base font-semibold">
                                Tampilan
                            </h2>
                            <p className="mt-0.5 text-sm text-muted-foreground">
                                Pilih tema tampilan yang sesuai dengan
                                preferensi Anda.
                            </p>
                        </div>
                    </div>

                    <AppearanceTabs />
                </section>
            </div>
        </>
    );
}

Appearance.layout = {
    breadcrumbs: [
        {
            title: 'Pengaturan tampilan',
            href: settings.appearance.edit(),
        },
    ],
};

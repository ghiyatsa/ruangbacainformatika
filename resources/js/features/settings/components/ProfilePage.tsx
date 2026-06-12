import { Head, usePage } from '@inertiajs/react';
import { BadgeCheck, Clock3, IdCard, MapPin } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { ProfileInformationForm } from '@/features/settings/components/profile/ProfileInformationForm';
import { ProfileSummary } from '@/features/settings/components/profile/ProfileSummary';
import type { Auth } from '@/types';

export default function ProfilePage() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const user = auth.user!;
    const profileItems = [
        {
            label: 'Profil dasar',
            value: user.whatsapp && user.address ? 'Lengkap' : 'Perlu dilengkapi',
            icon: IdCard,
            variant:
                user.whatsapp && user.address ? ('success' as const) : ('outline' as const),
        },
        {
            label: 'Verifikasi WhatsApp',
            value: auth.hasVerifiedWhatsApp ? 'Sudah aktif' : 'Belum aktif',
            icon: BadgeCheck,
            variant: auth.hasVerifiedWhatsApp
                ? ('success' as const)
                : ('outline' as const),
        },
        {
            label: 'Layanan pinjam',
            value: auth.canBorrowBooks ? 'Dapat digunakan' : 'Belum tersedia',
            icon: Clock3,
            variant: auth.canBorrowBooks ? ('success' as const) : ('outline' as const),
        },
        {
            label: 'Alamat',
            value: user.address ? 'Sudah diisi' : 'Belum diisi',
            icon: MapPin,
            variant: user.address ? ('success' as const) : ('outline' as const),
        },
    ];

    return (
        <>
            <Head title="Pengaturan profil" />
            <h1 className="sr-only">Pengaturan profil</h1>

            <div className="space-y-6">
                <div className="grid gap-4 lg:grid-cols-[minmax(0,1.25fr)_minmax(0,0.95fr)]">
                    <div className="rounded-xl border border-border/70 bg-card p-6 shadow-xs">
                        <ProfileSummary
                            name={user.name}
                            email={user.email}
                            avatar={user.avatar}
                            whatsapp={user.whatsapp}
                        />
                    </div>

                    <section className="rounded-xl border border-border/70 bg-card p-6 shadow-xs">
                        <div className="mb-4">
                            <h2 className="text-base font-semibold">
                                Status akun
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Ringkasan kesiapan akun untuk layanan anggota.
                            </p>
                        </div>

                        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                            {profileItems.map((item) => (
                                <div
                                    key={item.label}
                                    className="rounded-lg border border-border/70 bg-muted/20 p-4"
                                >
                                    <div className="mb-3 flex items-center gap-2">
                                        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-background text-muted-foreground">
                                            <item.icon className="h-4 w-4" />
                                        </div>
                                        <p className="text-sm font-medium">
                                            {item.label}
                                        </p>
                                    </div>
                                    <Badge variant={item.variant}>
                                        {item.value}
                                    </Badge>
                                </div>
                            ))}
                        </div>
                    </section>
                </div>

                <ProfileInformationForm user={user} />
            </div>
        </>
    );
}

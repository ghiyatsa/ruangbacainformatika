import { CheckCircle2, Mail, Phone, ShieldCheck } from 'lucide-react';

import { SettingsSectionHeader } from '@/features/settings/components/shared/SettingsSectionHeader';
import { ManageWhatsAppDialog } from './ManageWhatsAppDialog';
import type { User as AuthUser } from '@/types/auth';
import type { VerificationProps } from './ManageWhatsAppDialog';

interface AccountSecurityCardProps {
    user: AuthUser;
    verification?: VerificationProps | null;
}

export function AccountSecurityCard({
    user,
    verification,
}: AccountSecurityCardProps) {
    const isVerified = Boolean(user.whatsapp && user.whatsapp_verified_at);

    return (
        <section className="space-y-5">
            <SettingsSectionHeader title="Kontak & Keamanan Akun" />

            <div className="divide-y divide-border/60 rounded-xl border border-border/60 bg-card">
                {/* Email Section */}
                <div className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-3">
                        <div className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <Mail className="size-4.5" />
                        </div>
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="text-sm font-medium text-foreground">
                                    Email Akun
                                </span>
                                <span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                                    <ShieldCheck className="size-3" />
                                    Google SSO
                                </span>
                            </div>
                            <p className="text-sm text-muted-foreground">
                                {user.email}
                            </p>
                        </div>
                    </div>
                    <span className="text-xs text-muted-foreground sm:text-right">
                        Terkunci ke akun Google
                    </span>
                </div>

                {/* WhatsApp Section */}
                <div className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-3">
                        <div className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <Phone className="size-4.5" />
                        </div>
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="text-sm font-medium text-foreground">
                                    Nomor WhatsApp
                                </span>
                                {isVerified ? (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                                        <CheckCircle2 className="size-3" />
                                        Terverifikasi
                                    </span>
                                ) : user.whatsapp ? (
                                    <span className="rounded-full bg-amber-500/10 px-2 py-0.5 text-[11px] font-medium text-amber-600 dark:text-amber-400">
                                        Belum Terverifikasi
                                    </span>
                                ) : (
                                    <span className="rounded-full bg-muted px-2 py-0.5 text-[11px] font-medium text-muted-foreground">
                                        Belum Diisi
                                    </span>
                                )}
                            </div>
                            <p className="font-mono text-sm text-muted-foreground">
                                {user.whatsapp || '-'}
                            </p>
                        </div>
                    </div>

                    <div>
                        <ManageWhatsAppDialog
                            currentWhatsapp={user.whatsapp}
                            isVerified={isVerified}
                            verification={verification}
                        />
                    </div>
                </div>
            </div>
        </section>
    );
}

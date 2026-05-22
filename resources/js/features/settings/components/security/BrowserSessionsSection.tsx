import { Form } from '@inertiajs/react';
import { CheckCircle2, Monitor, Smartphone } from 'lucide-react';
import { useState } from 'react';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import InputError from '@/components/common/InputError';
import PasswordInput from '@/components/common/PasswordInput';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { SettingsSectionHeader } from '@/features/settings/components/shared/SettingsSectionHeader';
import type { Session } from '@/features/settings/types';
import { cn } from '@/lib/utils';

interface BrowserSessionsSectionProps {
    sessions: Session[];
}

export function BrowserSessionsSection({
    sessions,
}: BrowserSessionsSectionProps) {
    const [confirmingLogout, setConfirmingLogout] = useState(false);

    if (sessions.length === 0) {
        return null;
    }

    return (
        <section className="flex flex-col gap-6">
            <SettingsSectionHeader
                title="Sesi browser"
                description="Kelola dan keluarkan sesi aktif Anda di browser dan perangkat lain."
            />

            <div className="flex flex-col gap-4">
                <p className="text-sm text-muted-foreground">
                    Jika perlu, Anda dapat keluar dari semua sesi browser lain
                    di semua perangkat. Jika Anda merasa akun Anda telah
                    disusupi, sebaiknya perbarui kata sandi Anda juga.
                </p>

                <div className="flex flex-col gap-2">
                    {sessions.map((session) => (
                        <div
                            key={session.id}
                            className={cn(
                                'flex items-center gap-4 rounded-xl border p-4 transition-colors',
                                session.is_current_device
                                    ? 'border-primary/30 bg-primary/5'
                                    : 'bg-muted/40',
                            )}
                        >
                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-background text-muted-foreground shadow-xs">
                                {session.agent.is_desktop ? (
                                    <Monitor className="h-5 w-5" />
                                ) : (
                                    <Smartphone className="h-5 w-5" />
                                )}
                            </div>

                            <div className="min-w-0 flex-1">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="text-sm font-medium">
                                        {session.agent.platform} -{' '}
                                        {session.agent.browser}
                                    </span>
                                    {session.is_current_device ? (
                                        <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-950/60 dark:text-green-400">
                                            <CheckCircle2 className="h-3 w-3" />
                                            Perangkat ini
                                        </span>
                                    ) : null}
                                </div>
                                <p className="mt-0.5 text-xs text-muted-foreground">
                                    {session.ip_address}
                                    {!session.is_current_device ? (
                                        <> - {session.last_active}</>
                                    ) : null}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>

                <div className="mt-2">
                    <Dialog
                        open={confirmingLogout}
                        onOpenChange={setConfirmingLogout}
                    >
                        <DialogTrigger asChild>
                            <Button variant="outline">
                                Keluar dari Sesi Browser Lain
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    Keluar dari Sesi Browser Lain
                                </DialogTitle>
                                <DialogDescription>
                                    Masukkan kata sandi Anda untuk mengonfirmasi
                                    bahwa Anda ingin keluar dari sesi browser
                                    lain di semua perangkat.
                                </DialogDescription>
                            </DialogHeader>
                            <Form
                                action={SecurityController.destroy()}
                                onSuccess={() => setConfirmingLogout(false)}
                            >
                                {({ errors: formErrors, processing }) => (
                                    <div className="space-y-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="password_session">
                                                Kata sandi
                                            </Label>
                                            <PasswordInput
                                                id="password_session"
                                                name="password"
                                                placeholder="Masukkan kata sandi Anda"
                                            />
                                            <InputError
                                                message={formErrors.password}
                                            />
                                        </div>
                                        <DialogFooter>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={() =>
                                                    setConfirmingLogout(false)
                                                }
                                            >
                                                Batal
                                            </Button>
                                            <Button
                                                type="submit"
                                                disabled={processing}
                                            >
                                                Keluar dari Sesi Browser Lain
                                            </Button>
                                        </DialogFooter>
                                    </div>
                                )}
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>
        </section>
    );
}

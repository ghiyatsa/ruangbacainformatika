import { Form } from '@inertiajs/react';
import { KeyRound } from 'lucide-react';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import { KioskPanel } from '@/components/kiosk/KioskPanel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import type { KioskProps } from '@/pages/Kiosk/types';
import { KioskField } from './KioskField';

export function PinStep({ pageTitle, pageSubtitle, siteName }: KioskProps) {
    return (
        <div className="flex w-full max-w-md flex-col gap-4">
            <div className="flex flex-col items-center gap-2 text-center">
                <div className="flex size-10 items-center justify-center rounded-lg border bg-card">
                    <KeyRound className="size-5" />
                </div>
                <div>
                    <p className="text-sm text-muted-foreground">{siteName}</p>
                    <h1 className="text-2xl font-semibold">{pageTitle}</h1>
                </div>
            </div>

            <KioskPanel title="Masukkan PIN" description={pageSubtitle}>
                <Form
                    action={KioskController.verifyPin.url()}
                    method="post"
                    resetOnError
                    disableWhileProcessing
                    className="flex flex-col gap-5"
                >
                    {({ errors, processing }) => (
                        <>
                            <KioskField
                                label="PIN Kiosk"
                                htmlFor="pin"
                                error={errors.pin}
                                required
                            >
                                <Input
                                    id="pin"
                                    name="pin"
                                    type="password"
                                    inputMode="numeric"
                                    autoComplete="one-time-code"
                                    autoFocus
                                    maxLength={8}
                                    placeholder="Masukkan PIN"
                                    aria-invalid={Boolean(errors.pin)}
                                />
                            </KioskField>

                            <Button
                                type="submit"
                                size="lg"
                                disabled={processing}
                            >
                                {processing ? <Spinner /> : null}
                                Verifikasi PIN
                            </Button>
                        </>
                    )}
                </Form>
            </KioskPanel>
        </div>
    );
}

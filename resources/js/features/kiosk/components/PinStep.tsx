import { Form } from '@inertiajs/react';
import { KeyRound, Library, Sparkles } from 'lucide-react';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { KioskField } from '@/features/kiosk/components/KioskField';
import type { KioskProps } from '@/features/kiosk/types';

export function PinStep({ pageTitle, pageSubtitle, siteName }: KioskProps) {
    return (
        <div className="flex w-full max-w-sm flex-col gap-6">
            {/* Branding header */}
            <div className="flex flex-col items-center gap-3 text-center">
                <div className="flex size-14 items-center justify-center rounded-2xl border bg-linear-to-br from-primary/20 to-primary/5 text-primary shadow-sm">
                    <Library className="size-7" />
                </div>
                <div className="flex flex-col items-center gap-1.5">
                    <Badge variant="secondary" className="gap-1.5">
                        <Sparkles data-icon="inline-start" />
                        {siteName}
                    </Badge>
                    <h1 className="text-2xl font-bold tracking-tight">
                        {pageTitle}
                    </h1>
                </div>
            </div>

            {/* PIN card */}
            <Card className="border-border/60 shadow-md">
                <CardHeader className="pb-4">
                    <div className="flex items-center gap-2.5">
                        <div className="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <KeyRound className="size-4" />
                        </div>
                        <div>
                            <CardTitle className="text-base">
                                Masukkan PIN
                            </CardTitle>
                            {pageSubtitle && (
                                <CardDescription className="mt-0.5 text-xs">
                                    {pageSubtitle}
                                </CardDescription>
                            )}
                        </div>
                    </div>
                </CardHeader>

                <CardContent>
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
                                        className="text-center tracking-widest text-lg"
                                        aria-invalid={Boolean(errors.pin)}
                                    />
                                </KioskField>

                                <Button
                                    type="submit"
                                    size="lg"
                                    className="w-full"
                                    disabled={processing}
                                >
                                    {processing ? <Spinner /> : null}
                                    Verifikasi PIN
                                </Button>
                            </>
                        )}
                    </Form>
                </CardContent>
            </Card>
        </div>
    );
}

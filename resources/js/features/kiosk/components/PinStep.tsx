import { Form } from '@inertiajs/react';
import { ArrowRight, KeyRound, Library } from 'lucide-react';
import { useState } from 'react';
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

export function PinStep({
    pageTitle,
    pageSubtitle,
    siteName,
    siteTagline,
}: KioskProps) {
    const [pin, setPin] = useState('');

    return (
        <div className="mx-auto grid w-full max-w-5xl items-center gap-6 lg:grid-cols-[minmax(0,1.05fr)_420px]">
            <section className="space-y-5">
                <Badge variant="secondary" className="gap-2">
                    <Library className="size-4" />
                    {siteName}
                </Badge>

                <div className="space-y-3">
                    <h1 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        {pageTitle}
                    </h1>
                    <p className="max-w-xl text-sm leading-6 text-muted-foreground sm:text-base">
                        {pageSubtitle}
                    </p>
                </div>

                {siteTagline ? (
                    <p className="text-sm text-muted-foreground">
                        {siteTagline}
                    </p>
                ) : null}
            </section>

            <Card className="border-border/70">
                <CardHeader>
                    <div className="flex items-center gap-3">
                        <div className="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <KeyRound className="size-4" />
                        </div>
                        <div>
                            <CardTitle>Masukkan PIN</CardTitle>
                            <CardDescription>
                                Gunakan PIN untuk membuka layanan.
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <Form
                        action={KioskController.verifyPin.url()}
                        method="post"
                        resetOnError
                        disableWhileProcessing
                        autoComplete="off"
                        className="flex flex-col gap-4"
                    >
                        {({ errors, processing }) => (
                            <>
                                <input
                                    type="text"
                                    name="username"
                                    autoComplete="username"
                                    tabIndex={-1}
                                    className="hidden"
                                    aria-hidden="true"
                                />
                                <input
                                    type="password"
                                    name="password"
                                    autoComplete="current-password"
                                    tabIndex={-1}
                                    className="hidden"
                                    aria-hidden="true"
                                />

                                <KioskField
                                    label="PIN"
                                    htmlFor="pin"
                                    error={errors.pin}
                                    required
                                >
                                    <input type="hidden" name="pin" value={pin} />
                                    <Input
                                        id="pin"
                                        type="password"
                                        inputMode="numeric"
                                        autoComplete="new-password"
                                        autoCorrect="off"
                                        spellCheck={false}
                                        data-lpignore="true"
                                        data-1p-ignore="true"
                                        data-bwignore="true"
                                        autoFocus
                                        maxLength={8}
                                        placeholder="••••••"
                                        className="h-12 text-center text-lg tracking-[0.35em]"
                                        value={pin}
                                        onChange={(event) =>
                                            setPin(event.target.value)
                                        }
                                        aria-invalid={Boolean(errors.pin)}
                                    />
                                </KioskField>

                                <Button
                                    type="submit"
                                    size="lg"
                                    className="w-full"
                                    disabled={processing}
                                >
                                    {processing ? (
                                        <Spinner />
                                    ) : (
                                        <ArrowRight className="size-4" />
                                    )}
                                    Lanjut
                                </Button>
                            </>
                        )}
                    </Form>
                </CardContent>
            </Card>
        </div>
    );
}

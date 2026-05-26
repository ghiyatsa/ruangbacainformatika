import { useForm } from '@inertiajs/react';
import { QrCode, UserIcon, MailIcon, PhoneIcon } from 'lucide-react';
import { useState } from 'react';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { FieldGroup } from '@/components/ui/field';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
    InputGroupTextarea,
} from '@/components/ui/input-group';
import { Spinner } from '@/components/ui/spinner';
import { KioskField } from '@/features/kiosk/components/KioskField';
import { MemberRegistrationClaimDialog } from '@/features/kiosk/components/MemberRegistrationClaimDialog';
import type { KioskMemberRegistrationClaim } from '@/features/kiosk/types';

interface MemberFormProps {
    memberRegistrationClaim?: KioskMemberRegistrationClaim | null;
}

const ALLOWED_EMAIL_DOMAINS = ['mhs.unimal.ac.id', 'unimal.ac.id'] as const;

const INITIAL_FORM_DATA = {
    name: '',
    email: '',
    whatsapp: '',
    address: '',
};

const DEFAULT_EMAIL_DOMAIN = '@mhs.unimal.ac.id';

const normalizeEmailDomain = (domain: string): string =>
    domain.trim().toLowerCase().replace(/^@+/, '');

const composeMemberEmail = (localPart: string, domain: string): string => {
    const normalizedLocalPart = localPart.trim().toLowerCase();
    const normalizedDomain = normalizeEmailDomain(domain);

    if (normalizedLocalPart === '' && normalizedDomain === '') {
        return '';
    }

    return `${normalizedLocalPart}@${normalizedDomain}`;
};

const splitMemberEmail = (
    email: string,
): { localPart: string; domain: string } => {
    const normalizedEmail = email.trim().toLowerCase();

    if (normalizedEmail === '') {
        return {
            localPart: '',
            domain: DEFAULT_EMAIL_DOMAIN,
        };
    }

    const [localPart = '', domain = DEFAULT_EMAIL_DOMAIN] =
        normalizedEmail.split('@');

    return {
        localPart,
        domain: `@${normalizeEmailDomain(domain || DEFAULT_EMAIL_DOMAIN)}`,
    };
};

const getMemberEmailError = (
    localPart: string,
    domain: string,
): string | null => {
    const normalizedEmail = composeMemberEmail(localPart, domain);

    if (normalizedEmail === '') {
        return 'Email wajib diisi.';
    }

    const emailSegments = normalizedEmail.split('@');

    if (emailSegments.length !== 2) {
        return 'Masukkan email dengan format yang valid.';
    }

    const [parsedLocalPart, parsedDomain] = emailSegments;

    if (parsedLocalPart.trim() === '') {
        return 'Masukkan email dengan format yang valid.';
    }

    if (
        !ALLOWED_EMAIL_DOMAINS.includes(
            parsedDomain as (typeof ALLOWED_EMAIL_DOMAINS)[number],
        )
    ) {
        return 'Gunakan email UNIMAL dengan domain @mhs.unimal.ac.id atau @unimal.ac.id.';
    }

    return null;
};

export function MemberForm({ memberRegistrationClaim }: MemberFormProps) {
    const form = useForm(INITIAL_FORM_DATA);
    const [emailParts, setEmailParts] = useState(() =>
        splitMemberEmail(INITIAL_FORM_DATA.email),
    );
    const [isClaimDialogOpen, setIsClaimDialogOpen] = useState(
        Boolean(memberRegistrationClaim),
    );
    const [activeRegistration, setActiveRegistration] =
        useState<KioskMemberRegistrationClaim | null>(
            memberRegistrationClaim ?? null,
        );
    const [prevClaim, setPrevClaim] = useState<
        KioskMemberRegistrationClaim | null | undefined
    >(memberRegistrationClaim);

    if (memberRegistrationClaim !== prevClaim) {
        setPrevClaim(memberRegistrationClaim);
        setActiveRegistration(memberRegistrationClaim ?? null);

        if (
            memberRegistrationClaim &&
            memberRegistrationClaim.status !== 'claimed' &&
            memberRegistrationClaim.status !== 'linked'
        ) {
            setIsClaimDialogOpen(true);
        }
    }

    const isPendingClaim = activeRegistration?.status === 'pending';
    const isLinkedClaim = activeRegistration?.status === 'linked';
    const isExpired = activeRegistration?.status === 'expired';

    const isComplete =
        form.data.name.trim() !== '' &&
        emailParts.localPart.trim() !== '' &&
        emailParts.domain.trim() !== '' &&
        form.data.whatsapp.trim() !== '' &&
        form.data.address.trim() !== '';

    const resetMemberForm = () => {
        form.reset();
        form.clearErrors();
        form.setData(INITIAL_FORM_DATA);
        setEmailParts(splitMemberEmail(INITIAL_FORM_DATA.email));
    };

    const updateEmail = (
        nextParts: Partial<{ localPart: string; domain: string }>,
    ) => {
        setEmailParts((currentParts) => {
            let updatedParts = {
                ...currentParts,
                ...nextParts,
            };

            if (nextParts.localPart && nextParts.localPart.includes('@')) {
                const parsed = splitMemberEmail(nextParts.localPart);
                updatedParts = {
                    localPart: parsed.localPart,
                    domain: parsed.domain,
                };
            }

            form.setData(
                'email',
                composeMemberEmail(updatedParts.localPart, updatedParts.domain),
            );

            if (form.errors.email) {
                const emailError = getMemberEmailError(
                    updatedParts.localPart,
                    updatedParts.domain,
                );

                if (emailError === null) {
                    form.clearErrors('email');
                } else {
                    form.setError('email', emailError);
                }
            }

            return updatedParts;
        });
    };

    const validateEmail = () => {
        const emailError = getMemberEmailError(
            emailParts.localPart,
            emailParts.domain,
        );

        if (emailError === null) {
            form.clearErrors('email');

            return true;
        }

        form.setError('email', emailError);

        return false;
    };

    const submitMemberRegistrationRequest = () => {
        form.post(KioskController.storeMember.url(), {
            preserveScroll: true,
            onSuccess: () => {
                setIsClaimDialogOpen(true);
            },
        });
    };

    const handleLinked = () => {
        resetMemberForm();
        setActiveRegistration(null);
    };

    const handleCancel = () => {
        resetMemberForm();
        setActiveRegistration(null);
    };

    const handleRestart = () => {
        submitMemberRegistrationRequest();
    };

    const handleDialogOpenChange = (open: boolean) => {
        setIsClaimDialogOpen(open);

        if (!open && activeRegistration && !isPendingClaim && !isLinkedClaim) {
            setActiveRegistration(null);
        }
    };

    const submitMemberRegistration = (
        event: React.FormEvent<HTMLFormElement>,
    ) => {
        event.preventDefault();

        if (!validateEmail()) {
            return;
        }

        submitMemberRegistrationRequest();
    };

    return (
        <div className="flex flex-col gap-6">
            {activeRegistration && !isClaimDialogOpen ? (
                <div className="rounded-3xl border border-border/70 bg-muted/20 p-5">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div className="space-y-2">
                            <Badge variant="outline">
                                {isPendingClaim
                                    ? 'Proses masih berjalan'
                                    : isLinkedClaim
                                      ? 'Registrasi selesai'
                                      : isExpired
                                        ? 'Waktu habis'
                                        : 'Registrasi berhasil'}
                            </Badge>
                            <div className="space-y-1">
                                <p className="text-base font-semibold text-foreground">
                                    Registrasi {activeRegistration.name}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    {isPendingClaim
                                        ? 'QR masih aktif.'
                                        : isLinkedClaim
                                          ? 'Akun sudah tertaut.'
                                          : isExpired
                                            ? 'QR sudah kedaluwarsa.'
                                            : 'Form siap digunakan lagi.'}
                                </p>
                            </div>
                        </div>

                        <Button
                            type="button"
                            variant={isPendingClaim ? 'default' : 'secondary'}
                            onClick={() => setIsClaimDialogOpen(true)}
                        >
                            {isPendingClaim ? (
                                <QrCode className="size-4" />
                            ) : null}
                            {isPendingClaim ? 'Buka QR' : 'Lihat status'}
                        </Button>
                    </div>
                </div>
            ) : null}

            <form
                onSubmit={submitMemberRegistration}
                className="flex flex-col gap-4"
                autoComplete="off"
            >
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
                <FieldGroup className="grid gap-4 sm:grid-cols-2">
                    <KioskField
                        label="Nama Lengkap"
                        htmlFor="reg-name"
                        error={form.errors.name}
                        required
                    >
                        <InputGroup>
                            <InputGroupInput
                                id="reg-name"
                                autoFocus
                                autoComplete="new-password"
                                autoCapitalize="words"
                                autoCorrect="off"
                                spellCheck={false}
                                data-lpignore="true"
                                data-1p-ignore="true"
                                data-bwignore="true"
                                placeholder="Nama lengkap"
                                value={form.data.name}
                                onChange={(event) =>
                                    form.setData('name', event.target.value)
                                }
                                aria-invalid={Boolean(form.errors.name)}
                                disabled={
                                    form.processing ||
                                    isPendingClaim ||
                                    isLinkedClaim
                                }
                            />

                            <InputGroupAddon>
                                <UserIcon />
                            </InputGroupAddon>
                        </InputGroup>
                    </KioskField>

                    <KioskField
                        label="Email"
                        htmlFor="reg-email"
                        error={form.errors.email}
                        required
                    >
                        <InputGroup>
                            <InputGroupInput
                                id="reg-email"
                                type="text"
                                autoComplete="new-password"
                                autoCapitalize="none"
                                autoCorrect="off"
                                spellCheck={false}
                                data-lpignore="true"
                                data-1p-ignore="true"
                                data-bwignore="true"
                                placeholder="Email"
                                value={emailParts.localPart}
                                onChange={(event) =>
                                    updateEmail({
                                        localPart: event.target.value,
                                    })
                                }
                                onBlur={validateEmail}
                                aria-invalid={Boolean(form.errors.email)}
                                className="min-w-0"
                                disabled={
                                    form.processing ||
                                    isPendingClaim ||
                                    isLinkedClaim
                                }
                            />
                            <InputGroupAddon align={'inline-start'}>
                                <MailIcon />
                            </InputGroupAddon>
                            <InputGroupInput
                                id="reg-email-domain"
                                type="text"
                                autoComplete="off"
                                autoCapitalize="none"
                                autoCorrect="off"
                                spellCheck={false}
                                data-lpignore="true"
                                data-1p-ignore="true"
                                data-bwignore="true"
                                placeholder="mhs.unimal.ac.id"
                                value={emailParts.domain}
                                onChange={(event) =>
                                    updateEmail({
                                        domain: event.target.value,
                                    })
                                }
                                onBlur={validateEmail}
                                aria-invalid={Boolean(form.errors.email)}
                                className="max-w-34 border-l border-input pl-3"
                                disabled={
                                    form.processing ||
                                    isPendingClaim ||
                                    isLinkedClaim
                                }
                            />
                        </InputGroup>
                    </KioskField>

                    <KioskField
                        label="No. WhatsApp"
                        htmlFor="reg-whatsapp"
                        error={form.errors.whatsapp}
                        required
                    >
                        <InputGroup>
                            <InputGroupInput
                                id="reg-whatsapp"
                                type="tel"
                                autoComplete="new-password"
                                autoCapitalize="none"
                                autoCorrect="off"
                                spellCheck={false}
                                data-lpignore="true"
                                data-1p-ignore="true"
                                data-bwignore="true"
                                placeholder="08123456789"
                                value={form.data.whatsapp}
                                onChange={(event) =>
                                    form.setData('whatsapp', event.target.value)
                                }
                                aria-invalid={Boolean(form.errors.whatsapp)}
                                disabled={
                                    form.processing ||
                                    isPendingClaim ||
                                    isLinkedClaim
                                }
                            />
                            <InputGroupAddon>
                                <PhoneIcon />
                            </InputGroupAddon>
                        </InputGroup>
                    </KioskField>

                    <KioskField
                        label="Alamat"
                        htmlFor="reg-address"
                        error={form.errors.address}
                        required
                        className="sm:col-span-2"
                    >
                        <InputGroup>
                            <InputGroupTextarea
                                id="reg-address"
                                autoComplete="new-password"
                                autoCorrect="off"
                                spellCheck={false}
                                data-lpignore="true"
                                data-1p-ignore="true"
                                data-bwignore="true"
                                placeholder="Masukkan alamat lengkap"
                                value={form.data.address}
                                onChange={(event) =>
                                    form.setData('address', event.target.value)
                                }
                                aria-invalid={Boolean(form.errors.address)}
                                className="min-h-28 resize-y"
                                disabled={
                                    form.processing ||
                                    isPendingClaim ||
                                    isLinkedClaim
                                }
                            />
                        </InputGroup>
                    </KioskField>
                </FieldGroup>

                <Button
                    type="submit"
                    size="lg"
                    disabled={
                        form.processing ||
                        !isComplete ||
                        isPendingClaim ||
                        isLinkedClaim
                    }
                >
                    {form.processing ? (
                        <Spinner />
                    ) : (
                        <QrCode className="size-4" />
                    )}
                    {form.processing ? 'Menyiapkan QR...' : 'Tampilkan QR'}
                </Button>
            </form>

            {activeRegistration ? (
                <MemberRegistrationClaimDialog
                    isOpen={isClaimDialogOpen}
                    onOpenChange={handleDialogOpenChange}
                    initialRegistration={activeRegistration}
                    onLinked={handleLinked}
                    onCancel={handleCancel}
                    onRestart={handleRestart}
                />
            ) : null}
        </div>
    );
}

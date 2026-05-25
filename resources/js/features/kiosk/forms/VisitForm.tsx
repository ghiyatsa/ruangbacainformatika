import { Form } from '@inertiajs/react';
import { useState } from 'react';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import { Button } from '@/components/ui/button';
import { FieldGroup } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { KioskField } from '@/features/kiosk/components/KioskField';

export function VisitForm({
    visitorTypeOptions,
    purposeOptions,
}: {
    visitorTypeOptions: Record<string, string>;
    purposeOptions: Record<string, string>;
}) {
    const [name, setName] = useState('');
    const [visitorType, setVisitorType] = useState('');
    const [identityNumber, setIdentityNumber] = useState('');
    const [institution, setInstitution] = useState('');
    const [phone, setPhone] = useState('');
    const [notes, setNotes] = useState('');
    const [purpose, setPurpose] = useState('');

    const isPublicVisitor = visitorType === 'umum';
    const requiresIdentity = visitorType !== '' && !isPublicVisitor;

    const isComplete =
        name.trim() !== '' &&
        visitorType !== '' &&
        purpose !== '' &&
        (requiresIdentity ? identityNumber.trim() !== '' : true) &&
        (isPublicVisitor ? institution.trim() !== '' : true);

    return (
        <Form
            action={KioskController.store.url()}
            method="post"
            resetOnSuccess
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
                    <FieldGroup className="grid gap-4 sm:grid-cols-2">
                        <KioskField
                            label="Nama Lengkap"
                            htmlFor="visit-name"
                            error={errors.name}
                            required
                        >
                            <input type="hidden" name="name" value={name} />
                            <Input
                                id="visit-name"
                                autoFocus
                                autoComplete="new-password"
                                autoCorrect="off"
                                spellCheck={false}
                                data-lpignore="true"
                                data-1p-ignore="true"
                                data-bwignore="true"
                                placeholder="Nama lengkap"
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                                aria-invalid={Boolean(errors.name)}
                            />
                        </KioskField>

                        <KioskField
                            label="Jenis Pengunjung"
                            htmlFor="visit-visitor-type"
                            error={errors.visitor_type}
                            required
                        >
                            <Select
                                name="visitor_type"
                                value={visitorType}
                                onValueChange={(v) => {
                                    setVisitorType(v);
                                    setIdentityNumber('');
                                    setInstitution('');
                                }}
                                required
                            >
                                <SelectTrigger
                                    id="visit-visitor-type"
                                    className="w-full"
                                    aria-invalid={Boolean(errors.visitor_type)}
                                >
                                    <SelectValue placeholder="Pilih jenis" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        {Object.entries(visitorTypeOptions).map(
                                            ([value, label]) => (
                                                <SelectItem
                                                    key={value}
                                                    value={value}
                                                >
                                                    {label}
                                                </SelectItem>
                                            ),
                                        )}
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </KioskField>

                        {requiresIdentity ? (
                            <KioskField
                                label="NIM / NIP"
                                htmlFor="visit-identity"
                                error={errors.identity_number}
                                required
                            >
                                <input
                                    type="hidden"
                                    name="identity_number"
                                    value={identityNumber}
                                />
                                <Input
                                    id="visit-identity"
                                    autoComplete="new-password"
                                    autoCorrect="off"
                                    spellCheck={false}
                                    data-lpignore="true"
                                    data-1p-ignore="true"
                                    data-bwignore="true"
                                    placeholder="Nomor identitas"
                                    value={identityNumber}
                                    onChange={(e) =>
                                        setIdentityNumber(e.target.value)
                                    }
                                    aria-invalid={Boolean(
                                        errors.identity_number,
                                    )}
                                />
                            </KioskField>
                        ) : null}

                        {isPublicVisitor ? (
                            <KioskField
                                label="Instansi"
                                htmlFor="visit-institution"
                                error={errors.institution}
                                required
                            >
                                <input
                                    type="hidden"
                                    name="institution"
                                    value={institution}
                                />
                                <Input
                                    id="visit-institution"
                                    autoComplete="new-password"
                                    autoCorrect="off"
                                    spellCheck={false}
                                    data-lpignore="true"
                                    data-1p-ignore="true"
                                    data-bwignore="true"
                                    placeholder="Asal instansi"
                                    value={institution}
                                    onChange={(e) =>
                                        setInstitution(e.target.value)
                                    }
                                    aria-invalid={Boolean(errors.institution)}
                                />
                            </KioskField>
                        ) : null}

                        <KioskField
                            label="No. Telepon"
                            htmlFor="visit-phone"
                            error={errors.phone}
                        >
                            <input type="hidden" name="phone" value={phone} />
                            <Input
                                id="visit-phone"
                                type="tel"
                                inputMode="numeric"
                                autoComplete="new-password"
                                autoCorrect="off"
                                spellCheck={false}
                                data-lpignore="true"
                                data-1p-ignore="true"
                                data-bwignore="true"
                                placeholder="08xxxxxxxxxx"
                                value={phone}
                                onChange={(e) => setPhone(e.target.value)}
                                aria-invalid={Boolean(errors.phone)}
                            />
                        </KioskField>

                        <KioskField
                            label="Tujuan Kunjungan"
                            htmlFor="visit-purpose"
                            error={errors.purpose}
                            required
                        >
                            <Select
                                name="purpose"
                                value={purpose}
                                onValueChange={setPurpose}
                                required
                            >
                                <SelectTrigger
                                    id="visit-purpose"
                                    className="w-full"
                                    aria-invalid={Boolean(errors.purpose)}
                                >
                                    <SelectValue placeholder="Pilih tujuan" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        {Object.entries(purposeOptions).map(
                                            ([value, label]) => (
                                                <SelectItem
                                                    key={value}
                                                    value={value}
                                                >
                                                    {label}
                                                </SelectItem>
                                            ),
                                        )}
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </KioskField>

                        <KioskField
                            label="Catatan"
                            htmlFor="visit-notes"
                            error={errors.notes}
                            className="sm:col-span-2"
                        >
                            <input type="hidden" name="notes" value={notes} />
                            <textarea
                                id="visit-notes"
                                rows={2}
                                autoComplete="new-password"
                                autoCorrect="off"
                                spellCheck={false}
                                data-lpignore="true"
                                data-1p-ignore="true"
                                data-bwignore="true"
                                placeholder="Keterangan tambahan"
                                value={notes}
                                onChange={(e) => setNotes(e.target.value)}
                                className="min-h-20 w-full rounded-lg border border-input bg-transparent px-3 py-2 text-sm transition-colors outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-destructive/20"
                                aria-invalid={Boolean(errors.notes)}
                            />
                        </KioskField>
                    </FieldGroup>

                    <Button
                        type="submit"
                        size="lg"
                        disabled={processing || !isComplete}
                    >
                        {processing ? <Spinner /> : null}
                        Simpan Kunjungan
                    </Button>
                </>
            )}
        </Form>
    );
}

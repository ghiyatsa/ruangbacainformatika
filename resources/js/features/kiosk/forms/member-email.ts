import type { KioskMemberRegistrationClaim } from '@/features/kiosk/types';

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

export const ALLOWED_EMAIL_DOMAINS = [
    'mhs.unimal.ac.id',
    'unimal.ac.id',
] as const;

/** Bare domain — no leading `@`. The UI input uses `@` as visual separator. */
export const DEFAULT_EMAIL_DOMAIN = 'mhs.unimal.ac.id';

export const INITIAL_FORM_DATA = {
    name: '',
    email: '',
    whatsapp: '',
    address: '',
};

// ---------------------------------------------------------------------------
// Email helpers
// ---------------------------------------------------------------------------

/** Strip leading `@` characters and lowercase the domain. */
export const normalizeEmailDomain = (domain: string): string =>
    domain.trim().toLowerCase().replace(/^@+/, '');

/** Join a local-part and domain into a full email address. */
export const composeMemberEmail = (
    localPart: string,
    domain: string,
): string => {
    const trimmed = localPart.trim().toLowerCase();
    const normalized = normalizeEmailDomain(domain);

    if (trimmed === '' && normalized === '') {
        return '';
    }

    return `${trimmed}@${normalized}`;
};

/** Split an email into `{ localPart, domain }` with a leading `@` on the domain. */
export const splitMemberEmail = (
    email: string,
): { localPart: string; domain: string } => {
    const trimmed = email.trim().toLowerCase();

    if (trimmed === '') {
        return { localPart: '', domain: `@${DEFAULT_EMAIL_DOMAIN}` };
    }

    const atIndex = trimmed.indexOf('@');
    const localPart = atIndex === -1 ? trimmed : trimmed.slice(0, atIndex);
    const rawDomain =
        atIndex === -1 ? DEFAULT_EMAIL_DOMAIN : trimmed.slice(atIndex + 1);

    return {
        localPart,
        domain: `@${normalizeEmailDomain(rawDomain)}`,
    };
};

/** Validate the two email input fields; returns an Indonesian error message or `null`. */
export const getMemberEmailError = (
    localPart: string,
    domain: string,
): string | null => {
    const trimmed = localPart.trim();

    if (trimmed === '' && normalizeEmailDomain(domain) === '') {
        return 'Email wajib diisi.';
    }

    if (trimmed === '') {
        return 'Masukkan email dengan format yang valid.';
    }

    const normalizedDomain = normalizeEmailDomain(domain);

    if (
        !ALLOWED_EMAIL_DOMAINS.includes(
            normalizedDomain as (typeof ALLOWED_EMAIL_DOMAINS)[number],
        )
    ) {
        return 'Gunakan email UNIMAL dengan domain @mhs.unimal.ac.id atau @unimal.ac.id.';
    }

    return null;
};

// ---------------------------------------------------------------------------
// Claim helpers
// ---------------------------------------------------------------------------

export const isInteractiveClaim = (
    claim: KioskMemberRegistrationClaim | null | undefined,
): claim is KioskMemberRegistrationClaim =>
    Boolean(claim && claim.status !== 'claimed' && claim.status !== 'linked');

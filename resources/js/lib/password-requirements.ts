export const PASSWORD_MIN_LENGTH = 8;

type PasswordRequirement = {
    key: string;
    label: string;
    test: (value: string) => boolean;
};

export const PASSWORD_REQUIREMENTS: PasswordRequirement[] = [
    {
        key: 'length',
        label: `Minimal ${PASSWORD_MIN_LENGTH} karakter`,
        test: (value) => value.length >= PASSWORD_MIN_LENGTH,
    },
    {
        key: 'letter',
        label: 'Memiliki huruf',
        test: (value) => /[A-Za-z]/.test(value),
    },
    {
        key: 'number',
        label: 'Memiliki angka',
        test: (value) => /\d/.test(value),
    },
];

export function evaluatePasswordRequirements(password: string) {
    return PASSWORD_REQUIREMENTS.map((requirement) => ({
        ...requirement,
        passed: requirement.test(password),
    }));
}

export function evaluatePasswordStrength(password: string) {
    const requirements = evaluatePasswordRequirements(password);
    const hasUppercase = /[A-Z]/.test(password);
    const hasLowercase = /[a-z]/.test(password);
    const hasSymbol = /[^A-Za-z0-9]/.test(password);
    const hasValidBase = requirements.every(
        (requirement) => requirement.passed,
    );

    let score = 0;

    if (password.length > 0) {
        score += 1;
    }

    if (
        requirements.find((requirement) => requirement.key === 'length')?.passed
    ) {
        score += 1;
    }

    if (
        requirements.find((requirement) => requirement.key === 'letter')?.passed
    ) {
        score += 1;
    }

    if (
        requirements.find((requirement) => requirement.key === 'number')?.passed
    ) {
        score += 1;
    }

    if (hasUppercase || hasSymbol || password.length >= 12) {
        score += 1;
    }

    if (hasUppercase && hasLowercase && hasSymbol) {
        score += 1;
    }

    if (password.length === 0) {
        return {
            score: 0,
            percent: 0,
            label: 'Belum diisi',
            hint: `Gunakan minimal ${PASSWORD_MIN_LENGTH} karakter dengan kombinasi huruf dan angka.`,
            tone: 'muted',
            requirements,
            isValid: false,
        } as const;
    }

    if (!hasValidBase || score <= 3) {
        return {
            score,
            percent: 33,
            label: 'Rendah',
            hint: `Password minimal harus ${PASSWORD_MIN_LENGTH} karakter dan mengandung huruf serta angka.`,
            tone: 'low',
            requirements,
            isValid: false,
        } as const;
    }

    if (score <= 5) {
        return {
            score,
            percent: 66,
            label: 'Sedang',
            hint: 'Sudah memenuhi syarat minimum. Tambahkan huruf besar, simbol, atau panjang password untuk membuatnya lebih kuat.',
            tone: 'medium',
            requirements,
            isValid: true,
        } as const;
    }

    return {
        score,
        percent: 100,
        label: 'Tinggi',
        hint: 'Password ini kuat dan sudah melewati syarat minimum.',
        tone: 'high',
        requirements,
        isValid: true,
    } as const;
}

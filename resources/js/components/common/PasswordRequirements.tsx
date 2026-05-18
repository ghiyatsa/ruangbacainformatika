import { Badge } from '@/components/ui/badge';
import { evaluatePasswordStrength } from '@/lib/password-requirements';
import { cn } from '@/lib/utils';

type PasswordRequirementsProps = {
    password: string;
    className?: string;
};

export default function PasswordRequirements({
    password,
    className,
}: PasswordRequirementsProps) {
    if (password.length === 0) {
        return null;
    }

    const strength = evaluatePasswordStrength(password);

    const meterClasses = {
        muted: 'bg-border',
        low: 'bg-rose-500',
        medium: 'bg-amber-500',
        high: 'bg-emerald-500',
    };

    const badgeClasses = {
        muted: 'border-border text-muted-foreground',
        low: 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300',
        medium: 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300',
        high: 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300',
    };

    return (
        <div
            className={cn(
                'rounded-xl border border-border/60 bg-muted/20 p-3',
                className,
            )}
        >
            <div className="flex items-center justify-between gap-3">
                <Badge
                    variant="outline"
                    className={cn(
                        'rounded-full px-2.5 py-0.5 text-[11px] font-medium',
                        badgeClasses[strength.tone],
                    )}
                >
                    {strength.label}
                </Badge>
                <div className="h-1.5 flex-1 overflow-hidden rounded-full bg-border/70">
                    <div
                        className={cn(
                            'h-full rounded-full transition-all duration-300',
                            meterClasses[strength.tone],
                        )}
                        style={{ width: `${strength.percent}%` }}
                    />
                </div>
            </div>
            <div className="sr-only" aria-live="polite">
                Kekuatan password {strength.label}
            </div>
        </div>
    );
}

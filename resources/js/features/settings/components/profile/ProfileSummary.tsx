import { User } from 'lucide-react';

interface ProfileSummaryProps {
    name: string;
    email: string;
}

export function ProfileSummary({ name, email }: ProfileSummaryProps) {
    const initials = (name ?? '')
        .split(' ')
        .slice(0, 2)
        .map((word) => word[0]?.toUpperCase() ?? '')
        .join('');

    return (
        <div className="flex items-center gap-4">
            <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-primary text-xl font-semibold text-primary-foreground shadow-md shadow-primary/20 select-none">
                {initials || <User className="h-7 w-7" />}
            </div>
            <div>
                <p className="text-base leading-tight font-semibold">{name}</p>
                <p className="text-sm text-muted-foreground">{email}</p>
            </div>
        </div>
    );
}

import { User } from 'lucide-react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';

interface ProfileSummaryProps {
    name: string;
    email: string;
    avatar?: string | null;
}

export function ProfileSummary({ name, email, avatar }: ProfileSummaryProps) {
    const initials = (name ?? '')
        .split(' ')
        .slice(0, 2)
        .map((word) => word[0]?.toUpperCase() ?? '')
        .join('');

    return (
        <div className="flex items-center gap-4">
            <Avatar className="h-16 w-16 rounded-2xl shadow-md shadow-primary/20">
                <AvatarImage
                    src={avatar ?? undefined}
                    alt={name}
                    className="object-cover"
                />
                <AvatarFallback className="rounded-2xl bg-primary text-xl font-semibold text-primary-foreground">
                    {initials || <User className="h-7 w-7" />}
                </AvatarFallback>
            </Avatar>
            <div>
                <p className="text-base leading-tight font-semibold">{name}</p>
                <p className="text-sm text-muted-foreground">{email}</p>
            </div>
        </div>
    );
}

import { Mail, Phone, User } from 'lucide-react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';

interface ProfileSummaryProps {
    name: string;
    email: string;
    avatar?: string | null;
    whatsapp?: string | null;
}

export function ProfileSummary({
    name,
    email,
    avatar,
    whatsapp,
}: ProfileSummaryProps) {
    const initials = (name ?? '')
        .split(' ')
        .slice(0, 2)
        .map((word) => word[0]?.toUpperCase() ?? '')
        .join('');

    return (
        <div className="flex flex-col gap-5 sm:flex-row sm:items-center">
            <Avatar className="h-18 w-18 rounded-2xl shadow-none">
                <AvatarImage
                    src={avatar ?? undefined}
                    alt={name}
                    className="object-cover"
                />
                <AvatarFallback className="rounded-2xl bg-primary text-xl font-semibold text-primary-foreground">
                    {initials || <User className="h-7 w-7" />}
                </AvatarFallback>
            </Avatar>
            <div className="min-w-0 flex-1">
                <p className="text-lg leading-tight font-semibold">{name}</p>
                <div className="mt-3 grid gap-2 text-sm text-muted-foreground">
                    <div className="flex items-center gap-2">
                        <Mail className="h-4 w-4 shrink-0" />
                        <span className="truncate">{email}</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <Phone className="h-4 w-4 shrink-0" />
                        <span>{whatsapp || 'Nomor WhatsApp belum diisi'}</span>
                    </div>
                </div>
            </div>
        </div>
    );
}

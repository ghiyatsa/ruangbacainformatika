import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';

export function UserAvatar({
    name,
    avatar,
}: {
    name: string;
    avatar?: string | null;
}) {
    const initials = name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

    return (
        <Avatar className="size-8 shadow-sm">
            <AvatarImage src={avatar ?? undefined} alt={name} />
            <AvatarFallback className="bg-primary text-xs font-bold text-primary-foreground">
                {initials}
            </AvatarFallback>
        </Avatar>
    );
}

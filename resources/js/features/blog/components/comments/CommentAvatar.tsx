import { useState } from 'react';

interface CommentAvatarProps {
    avatarUrl: string | null | undefined;
    initials: string | null | undefined;
    size?: 'sm' | 'md' | 'lg';
}

const SIZE_CLASSES: Record<NonNullable<CommentAvatarProps['size']>, string> = {
    sm: 'size-8 text-xs',
    md: 'size-10 text-sm',
    lg: 'size-12 text-base',
};

export function CommentAvatar({
    avatarUrl,
    initials,
    size = 'md',
}: CommentAvatarProps) {
    const [hasError, setHasError] = useState(false);

    const sizeClass = SIZE_CLASSES[size];
    const isValidAvatar =
        !!avatarUrl && avatarUrl !== 'null' && avatarUrl !== 'undefined';

    if (isValidAvatar && !hasError) {
        return (
            <img
                // Re-keying on the URL clears the broken-image state when the
                // avatar changes, replacing the old render-phase setState.
                key={avatarUrl}
                src={avatarUrl ?? undefined}
                alt=""
                onError={() => setHasError(true)}
                className={`rounded-full border border-border/40 object-cover ${sizeClass}`}
            />
        );
    }

    return (
        <div
            className={`flex items-center justify-center rounded-full bg-primary text-center font-bold text-primary-foreground ${sizeClass}`}
        >
            {initials ?? '?'}
        </div>
    );
}

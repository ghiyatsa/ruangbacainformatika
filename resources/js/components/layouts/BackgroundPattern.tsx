export function BackgroundPattern() {
    return (
        <div
            className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
            style={{
                backgroundImage:
                    'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                backgroundSize: '24px 24px',
            }}
        />
    );
}

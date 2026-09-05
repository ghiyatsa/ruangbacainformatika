interface CatalogHeaderProps {
    title: string;
}

/**
 * Standard header for catalog-style pages.
 */
export function CatalogHeader({ title }: CatalogHeaderProps) {
    return (
        <section className="relative overflow-hidden border-b bg-background">
            <div className="mx-auto max-w-7xl border-x border-border/60 px-4 py-12 text-center sm:px-6 sm:py-20 lg:px-8">
                <h1 className="text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl lg:text-6xl">
                    {title}
                </h1>
            </div>
        </section>
    );
}

import { Link } from '@inertiajs/react';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';

interface SkripsiCatalogHeaderProps {
    total: number;
}

export function SkripsiCatalogHeader({ total }: SkripsiCatalogHeaderProps) {
    return (
        <div className="relative -mt-20 overflow-hidden bg-linear-to-br from-primary/5 via-background to-muted/30 sm:-mt-28">
            <div className="absolute inset-0 bg-linear-to-b from-background/0 via-background/40 to-background" />
            <div className="relative mx-auto max-w-7xl px-6 pt-32 pb-12 sm:pt-40 lg:px-8">
                <Breadcrumb className="mb-8">
                    <BreadcrumbList>
                        <BreadcrumbItem>
                            <BreadcrumbLink asChild>
                                <Link href="/">Beranda</Link>
                            </BreadcrumbLink>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator />
                        <BreadcrumbItem>
                            <BreadcrumbPage>Katalog Skripsi</BreadcrumbPage>
                        </BreadcrumbItem>
                    </BreadcrumbList>
                </Breadcrumb>

                <div className="flex flex-col gap-4">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">
                            Katalog Skripsi
                        </h1>
                        <p className="mt-2 text-muted-foreground">
                            {total.toLocaleString('id-ID')} skripsi tersedia
                            dalam koleksi perpustakaan
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}

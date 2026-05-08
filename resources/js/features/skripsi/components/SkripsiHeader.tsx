import { Link } from '@inertiajs/react';
import { GraduationCap } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';

interface SkripsiHeaderProps {
    total: number;
}

export function SkripsiHeader({ total }: SkripsiHeaderProps) {
    return (
        <div className="relative -mt-20 overflow-hidden border-b bg-linear-to-br from-primary/5 via-background to-muted/30 sm:-mt-28">
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
                            <BreadcrumbPage>Skripsi</BreadcrumbPage>
                        </BreadcrumbItem>
                    </BreadcrumbList>
                </Breadcrumb>

                <div className="flex flex-col gap-4">
                    <Badge
                        variant="secondary"
                        className="w-fit gap-1.5 bg-primary/10 text-primary hover:bg-primary/15"
                    >
                        <GraduationCap className="size-3.5" />
                        Koleksi Akademik
                    </Badge>
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

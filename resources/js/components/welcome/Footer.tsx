import { Globe, Terminal } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function Footer() {
    return (
        <footer className="border-t py-12">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="flex flex-col items-center justify-between gap-6 md:flex-row">
                    <div className="flex items-center gap-2">
                        <Terminal className="size-5 text-primary" />
                        <span className="text-sm font-bold tracking-wider uppercase">
                            Ruang Baca
                        </span>
                    </div>
                    <p className="text-center text-xs text-muted-foreground">
                        &copy; {new Date().getFullYear()} Prodi Teknik
                        Informatika Universitas Malikussaleh. Built for
                        Informatics Excellence.
                    </p>
                    <div className="flex gap-4">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 rounded-full"
                            asChild
                        >
                            <a href="#">
                                <Globe className="size-4" />
                            </a>
                        </Button>
                    </div>
                </div>
            </div>
        </footer>
    );
}

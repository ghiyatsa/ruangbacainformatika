import { Globe, Mail } from 'lucide-react';

export function FooterBottom() {
    const year = new Date().getFullYear();

    return (
        <div className="flex flex-col items-center justify-between gap-4 border-t py-5 sm:flex-row">
            <p className="text-center text-xs text-muted-foreground sm:text-left">
                &copy; {year} Program Studi Teknik Informatika, Universitas
                Malikussaleh.
            </p>

            <div className="flex items-center gap-1">
                <a
                    href="https://www.unimal.ac.id"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Website Universitas Malikussaleh"
                    className="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors duration-150 hover:bg-primary/10 hover:text-primary"
                >
                    <Globe className="size-4" />
                </a>
                <a
                    href="mailto:informatika@unimal.ac.id"
                    aria-label="Email Program Studi Teknik Informatika"
                    className="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors duration-150 hover:bg-primary/10 hover:text-primary"
                >
                    <Mail className="size-4" />
                </a>
            </div>
        </div>
    );
}

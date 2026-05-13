import { FooterBottom } from './FooterBottom';
import { FooterBrand } from './FooterBrand';
import { FooterLinks } from './FooterLinks';

export default function Footer() {
    return (
        <footer className="relative overflow-hidden border-t">
            <div
                className="pointer-events-none absolute inset-0 -z-10"
                aria-hidden="true"
            >
                <div className="absolute -bottom-32 left-1/2 h-[400px] w-[700px] -translate-x-1/2 rounded-full bg-primary/5 blur-[100px] dark:bg-primary/8" />
            </div>

            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="grid gap-12 py-14 sm:py-16 md:grid-cols-2 lg:grid-cols-12 xl:grid-cols-12">
                    <FooterBrand />
                    <FooterLinks />
                </div>

                <FooterBottom />
            </div>
        </footer>
    );
}

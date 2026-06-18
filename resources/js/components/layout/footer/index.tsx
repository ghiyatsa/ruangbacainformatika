import { FooterBottom } from './FooterBottom';
import { FooterBrand } from './FooterBrand';
import { FooterLinks } from './FooterLinks';

export default function Footer() {
    return (
        <footer className="relative overflow-hidden border-t border-border/60">


            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="grid justify-between gap-12 py-14 sm:py-16 md:grid-cols-2 lg:grid-cols-12 xl:grid-cols-12">
                    <FooterBrand />
                    <FooterLinks />
                </div>
            </div>

            <FooterBottom />
        </footer>
    );
}

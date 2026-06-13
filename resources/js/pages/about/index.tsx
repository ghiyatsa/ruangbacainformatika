import { AboutPage } from '@/features/about/components/AboutPage';
import type { StaticPageProps } from '@/features/static-pages/types';

export default function About(props: StaticPageProps) {
    return <AboutPage {...props} />;
}

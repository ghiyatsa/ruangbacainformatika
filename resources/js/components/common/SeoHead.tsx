import { Head, usePage } from '@inertiajs/react';

type SiteProps = {
    name: string;
    site: {
        url: string;
        name: string;
        description: string;
        department: string;
        contactEmail: string;
        supportWhatsapp: string | null;
        address: string;
        keywords: string | null;
        robots: string;
        themeColor: string;
        logo: string | null;
        ogImage: string;
        ogImageType: string;
        ogImageWidth: number;
        ogImageHeight: number;
        icons: {
            favicon: string;
            faviconSvg: string;
            appleTouchIcon: string;
        };
    };
};

interface SeoHeadProps {
    title?: string;
    description?: string;
    image?: string;
    type?: 'website' | 'article';
    robots?: string;
    keywords?: string | string[];
}

function normalizeUrl(baseUrl: string, currentPath: string): string {
    const trimmedBaseUrl = baseUrl.replace(/\/+$/, '');
    const pathWithoutQuery = currentPath.split('?')[0];
    const normalizedPath = pathWithoutQuery.startsWith('/')
        ? pathWithoutQuery
        : `/${pathWithoutQuery}`;

    return `${trimmedBaseUrl}${normalizedPath}`;
}

export function SeoHead({
    title,
    description,
    image,
    type = 'website',
    robots,
    keywords,
}: SeoHeadProps) {
    const page = usePage<Partial<SiteProps>>();
    const site = page.props.site;
    const siteUrl =
        site?.url ||
        (typeof window !== 'undefined' ? window.location.origin : '');
    const canonicalUrl = normalizeUrl(siteUrl, page.url || '');
    const metaDescription = description ?? site?.description ?? '';
    const metaImage = image ?? site?.ogImage ?? '';
    const metaTitle = title
        ? page.props.name
            ? `${title} - ${page.props.name}`
            : title
        : (page.props.name ?? 'Ruang Baca');
    const metaRobots = robots ?? site?.robots ?? 'index, follow';
    const metaImageType = site?.ogImageType ?? 'image/jpeg';
    const metaImageWidth = String(site?.ogImageWidth ?? 1200);
    const metaImageHeight = String(site?.ogImageHeight ?? 630);
    const metaKeywords = Array.isArray(keywords)
        ? keywords.filter(Boolean).join(', ')
        : (keywords ?? site?.keywords ?? '');

    return (
        <Head title={title}>
            <meta
                head-key="description"
                name="description"
                content={metaDescription}
            />
            <meta head-key="robots" name="robots" content={metaRobots} />
            {metaKeywords ? (
                <meta
                    head-key="keywords"
                    name="keywords"
                    content={metaKeywords}
                />
            ) : null}
            <link head-key="canonical" rel="canonical" href={canonicalUrl} />

            <meta head-key="og:type" property="og:type" content={type} />
            <meta head-key="og:url" property="og:url" content={canonicalUrl} />
            <meta head-key="og:title" property="og:title" content={metaTitle} />
            <meta
                head-key="og:description"
                property="og:description"
                content={metaDescription}
            />
            <meta head-key="og:image" property="og:image" content={metaImage} />
            <meta
                head-key="og:image:type"
                property="og:image:type"
                content={metaImageType}
            />
            <meta
                head-key="og:image:width"
                property="og:image:width"
                content={metaImageWidth}
            />
            <meta
                head-key="og:image:height"
                property="og:image:height"
                content={metaImageHeight}
            />

            <meta
                head-key="twitter:card"
                property="twitter:card"
                content="summary_large_image"
            />
            <meta
                head-key="twitter:title"
                property="twitter:title"
                content={metaTitle}
            />
            <meta
                head-key="twitter:description"
                property="twitter:description"
                content={metaDescription}
            />
            <meta
                head-key="twitter:image"
                property="twitter:image"
                content={metaImage}
            />
        </Head>
    );
}

import { Head, usePage } from '@inertiajs/react';

type SiteProps = {
    name: string;
    site: {
        url: string;
        description: string;
        department: string;
        contactEmail: string;
        address: string;
        ogImage: string;
    };
};

interface SeoHeadProps {
    title?: string;
    description?: string;
    image?: string;
    type?: 'website' | 'article';
    robots?: string;
}

function normalizeUrl(baseUrl: string, currentPath: string): string {
    const trimmedBaseUrl = baseUrl.replace(/\/+$/, '');
    const normalizedPath = currentPath.startsWith('/')
        ? currentPath
        : `/${currentPath}`;

    return `${trimmedBaseUrl}${normalizedPath}`;
}

export function SeoHead({
    title,
    description,
    image,
    type = 'website',
    robots = 'index,follow',
}: SeoHeadProps) {
    const page = usePage<SiteProps>();
    const canonicalUrl = normalizeUrl(page.props.site.url, page.url);
    const metaDescription = description ?? page.props.site.description;
    const metaImage = image ?? page.props.site.ogImage;
    const metaTitle = title ? `${title} - ${page.props.name}` : page.props.name;

    return (
        <Head title={title}>
            <meta
                head-key="description"
                name="description"
                content={metaDescription}
            />
            <meta head-key="robots" name="robots" content={robots} />
            <link head-key="canonical" rel="canonical" href={canonicalUrl} />

            <meta head-key="og:type" property="og:type" content={type} />
            <meta head-key="og:url" property="og:url" content={canonicalUrl} />
            <meta head-key="og:title" property="og:title" content={metaTitle} />
            <meta
                head-key="og:description"
                property="og:description"
                content={metaDescription}
            />
            <meta
                head-key="og:image"
                property="og:image"
                content={metaImage}
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

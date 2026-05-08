import { Head } from '@inertiajs/react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

export function AboutPage() {
    return (
        <>
            <Head title="About Us - Ruang Baca" />
            <div className="container mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-3xl font-bold tracking-tight text-foreground">
                            About Ruang Baca
                        </CardTitle>
                        <CardDescription className="mt-2 text-lg">
                            Digital Library of Informatics Engineering,
                            Universitas Malikussaleh
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="prose prose-slate dark:prose-invert max-w-none">
                        <p className="mb-4 text-base leading-relaxed">
                            Ruang Baca is the dedicated digital library and
                            resource center for the Informatics Engineering
                            department at Universitas Malikussaleh. Our mission
                            is to provide students, faculty, and researchers
                            with comprehensive access to a vast collection of
                            academic literature, including textbooks, reference
                            materials, research papers, and thesis collections
                            (skripsi).
                        </p>
                        <p className="mb-4 text-base leading-relaxed">
                            We aim to foster a culture of continuous learning
                            and research excellence by offering a modern,
                            user-friendly platform where the academic community
                            can easily discover, borrow, and read materials
                            essential to their studies and projects.
                        </p>
                        <h3 className="mt-6 mb-3 text-xl font-semibold text-foreground">
                            Our Vision
                        </h3>
                        <p className="mb-4 text-base leading-relaxed">
                            To become a leading digital repository and knowledge
                            hub that empowers the Informatics Engineering
                            community with seamless access to high-quality
                            information resources, supporting innovation and
                            technological advancement.
                        </p>
                        <h3 className="mt-6 mb-3 text-xl font-semibold text-foreground">
                            Our Collections
                        </h3>
                        <ul className="mb-4 list-disc space-y-2 pl-6">
                            <li>
                                <strong>Books:</strong> A wide range of
                                programming, software engineering, networking,
                                and computer science textbooks.
                            </li>
                            <li>
                                <strong>Theses (Skripsi):</strong> An archive of
                                past student research projects, available for
                                reference and inspiration.
                            </li>
                            <li>
                                <strong>Journals & Articles:</strong> Selected
                                academic publications relevant to informatics.
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

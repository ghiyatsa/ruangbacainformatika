<?php

it('keeps accessible names on the book catalog filter comboboxes', function () {
    $filtersComponent = file_get_contents(
        resource_path('js/features/books/components/BookCatalogFilters.tsx')
    );
    $searchableFilterComponent = file_get_contents(
        resource_path('js/features/books/components/SearchableCatalogFilter.tsx')
    );

    expect($filtersComponent)->not->toBeFalse()
        ->and($searchableFilterComponent)->not->toBeFalse()
        ->and($filtersComponent)->toContain('triggerAriaLabel="Filter kategori buku"')
        ->and($filtersComponent)->toContain('aria-label="Filter tahun buku"')
        ->and($filtersComponent)->toContain('triggerAriaLabel="Filter penulis buku"')
        ->and($filtersComponent)->toContain('triggerAriaLabel="Filter penerbit buku"')
        ->and($searchableFilterComponent)->toContain('aria-label={triggerAriaLabel}')
        ->and($searchableFilterComponent)->toContain('role="combobox"')
        ->and($searchableFilterComponent)->toContain("touchAction: 'none'")
        ->and($searchableFilterComponent)->toContain('height: `${sheetHeight}px`')
        ->and($searchableFilterComponent)->toContain('max-h-[min(26rem,calc(100svh-10rem))]')
        ->and($searchableFilterComponent)->toContain('max-h-[min(34rem,calc(100svh-4rem))]')
        ->and($searchableFilterComponent)->toContain('const MOBILE_SHEET_MIN_HEIGHT = 320;')
        ->and($searchableFilterComponent)->not->toContain('<CommandGroup')
        ->and($searchableFilterComponent)->not->toContain('<CommandShortcut>')
        ->and($searchableFilterComponent)->not->toContain('<SheetHeader')
        ->and($searchableFilterComponent)->not->toContain('<SheetTitle')
        ->and($searchableFilterComponent)->not->toContain('<SheetDescription');
});

it('does not use a skipped heading level for book card titles in the catalog grid', function () {
    $component = file_get_contents(
        resource_path('js/features/books/components/BookCard.tsx')
    );

    expect($component)->not->toBeFalse()
        ->and($component)->not->toContain('<h3 className="line-clamp-2 text-sm leading-snug font-bold transition-colors group-hover:text-primary sm:text-sm">')
        ->and($component)->toContain('<p className="line-clamp-2 text-sm leading-snug font-bold transition-colors group-hover:text-primary sm:text-sm">');
});

it('renders disabled pagination items without forbidden aria attributes on anchors', function () {
    $component = file_get_contents(
        resource_path('js/components/ui/pagination.tsx')
    );

    expect($component)->not->toBeFalse()
        ->and($component)->toContain('if (disabled)')
        ->and($component)->toContain('<span')
        ->and($component)->toContain('role="link"')
        ->and($component)->toContain('aria-disabled="true"')
        ->and($component)->not->toContain('<a'.PHP_EOL.'        href={href}'.PHP_EOL.'        aria-current={isActive ? "page" : undefined}'.PHP_EOL.'        aria-disabled={disabled || undefined}');
});

it('keeps breadcrumb heroes closer to the header across catalog and detail pages', function () {
    $catalogHeader = file_get_contents(
        resource_path('js/features/books/components/CatalogHeader.tsx')
    );
    $bookDetailPage = file_get_contents(
        resource_path('js/features/books/components/BookDetailPage.tsx')
    );
    $academicWorkDetailPage = file_get_contents(
        resource_path('js/features/academic-works/components/AcademicWorkDetailPage.tsx')
    );
    $internshipReportDetailPage = file_get_contents(
        resource_path('js/features/internship-report/components/InternshipReportDetailPage.tsx')
    );
    $ktiTextWorkHero = file_get_contents(
        resource_path('js/components/kti/KtiTextWorkHero.tsx')
    );
    expect($catalogHeader)->toContain('pt-24')
        ->and($catalogHeader)->toContain('pb-12')
        ->and($catalogHeader)->toContain('sm:pt-30')
        ->and($catalogHeader)->toContain('mb-6')
        ->and($bookDetailPage)->toContain('pt-24')
        ->and($bookDetailPage)->toContain('pb-6')
        ->and($bookDetailPage)->toContain('sm:pt-30')
        ->and($bookDetailPage)->toContain('sm:pb-8')
        ->and($bookDetailPage)->toContain('sm:mb-6')
        ->and($academicWorkDetailPage)->toContain('KtiTextWorkHero')
        ->and($internshipReportDetailPage)->toContain('KtiTextWorkHero')
        ->and($ktiTextWorkHero)->toContain('pt-24')
        ->and($ktiTextWorkHero)->toContain('pb-6')
        ->and($ktiTextWorkHero)->toContain('sm:pb-8')
        ->and($ktiTextWorkHero)->toContain('sm:pt-30');
});

it('shows book card skeletons when mobile progressive pagination starts loading more results', function () {
    $mobileProgressivePagination = file_get_contents(
        resource_path('js/features/books/components/CatalogMobilePagination.tsx')
    );
    $bookCatalogPage = file_get_contents(
        resource_path('js/features/books/components/BookCatalogPage.tsx')
    );
    $filtersSkeleton = file_get_contents(
        resource_path('js/features/books/components/BookCatalogFiltersSkeleton.tsx')
    );

    expect($mobileProgressivePagination)->toContain('loadingFallback?: ReactNode;')
        ->and($mobileProgressivePagination)->toContain("import { InfiniteScroll } from '@inertiajs/react';")
        ->and($mobileProgressivePagination)->toContain('<InfiniteScroll data={propKey}>')
        ->and($mobileProgressivePagination)->toContain('{loadingFallback}')
        ->and($bookCatalogPage)->toContain('loadingFallback={')
        ->and($bookCatalogPage)->toContain('fallback={<BookCatalogFiltersSkeleton />}')
        ->and($bookCatalogPage)->toContain("data={['categories', 'authors', 'publishers', 'years']}")
        ->and($bookCatalogPage)->toContain('const LazyBookCatalogFilters = lazy(async () => {')
        ->and($bookCatalogPage)->toContain('Array.from({ length: 6 })')
        ->and($bookCatalogPage)->toContain('Array.from({ length: 4 })')
        ->and($bookCatalogPage)->toContain("viewMode === 'list' ? 'compact' : 'grid'")
        ->and($bookCatalogPage)->toContain('className="hidden md:block"')
        ->and($filtersSkeleton)->toContain('aria-hidden="true"');
});

it('replaces the old welcome category-only surfaces with popular category book shelves', function () {
    $welcomePage = file_get_contents(
        resource_path('js/features/welcome/components/WelcomePage.tsx')
    );
    $catalogSection = file_get_contents(
        resource_path('js/features/welcome/components/CatalogSection.tsx')
    );
    $popularCategoryShelves = file_get_contents(
        resource_path('js/features/welcome/components/PopularCategoryShelves.tsx')
    );

    expect($welcomePage)->not->toBeFalse()
        ->and($welcomePage)->not->toContain('CategoryMarquee')
        ->and($welcomePage)->not->toContain('marqueeCategories')
        ->and($catalogSection)->toContain('<PopularCategoryShelves')
        ->and($catalogSection)->not->toContain('<PopularCategories')
        ->and($popularCategoryShelves)->toContain('dataKey="popularCategoryShelves"')
        ->and($popularCategoryShelves)->toContain('Array.from({ length: 3 })')
        ->and($popularCategoryShelves)->toContain('Lihat semua buku')
        ->and($popularCategoryShelves)->toContain('skeletonCount={6}');
});

it('renders a deferred most-borrowed books section on the welcome page', function () {
    $catalogSection = file_get_contents(
        resource_path('js/features/welcome/components/CatalogSection.tsx')
    );
    $mostBorrowedBooks = file_get_contents(
        resource_path('js/features/welcome/components/MostBorrowedBooks.tsx')
    );

    expect($catalogSection)->not->toBeFalse()
        ->and($mostBorrowedBooks)->not->toBeFalse()
        ->and($catalogSection)->toContain('<MostBorrowedBooks')
        ->and($mostBorrowedBooks)->toContain('dataKey="mostBorrowedBooks"')
        ->and($mostBorrowedBooks)->toContain('title="Paling Sering Dipinjam"')
        ->and($mostBorrowedBooks)->toContain('skeletonCount={6}');
});

it('does not use a skipped heading level for academic work card titles in the catalog grid', function () {
    $component = file_get_contents(
        resource_path('js/features/academic-works/components/AcademicWorkCard.tsx')
    );

    expect($component)->not->toBeFalse()
        ->and($component)->not->toContain('<h3 className="line-clamp-3 text-sm leading-snug font-bold transition-colors group-hover:text-primary">')
        ->and($component)->toContain('<p className="line-clamp-3 text-sm leading-snug font-bold transition-colors group-hover:text-primary">');
});

it('uses the concrete deferred prop names for academic work catalog data', function () {
    $component = file_get_contents(
        resource_path('js/features/academic-works/components/AcademicWorkCatalogPage.tsx')
    );

    expect($component)->not->toBeFalse()
        ->and($component)->toContain("const dataProp = workType === 'skripsi' ? 'skripsis' : 'theses';")
        ->and($component)->toContain('deferredData={dataProp}')
        ->and($component)->toContain('propKey={dataProp}')
        ->and($component)->not->toContain('deferredData="academicWorks"')
        ->and($component)->not->toContain('propKey="academicWorks"');
});

it('allows skeleton values inside KTI detail items without invalid paragraph nesting', function () {
    $component = file_get_contents(
        resource_path('js/components/kti/KtiDetailItem.tsx')
    );

    expect($component)->not->toBeFalse()
        ->and($component)->toContain('<div className="mt-0.5 truncate text-sm font-semibold text-foreground">')
        ->and($component)->not->toContain('<p className="mt-0.5 truncate text-sm font-semibold text-foreground">');
});

it('does not use a skipped heading level for internship report card titles in the catalog grid', function () {
    $component = file_get_contents(
        resource_path('js/features/internship-report/components/InternshipReportCard.tsx')
    );

    expect($component)->not->toBeFalse()
        ->and($component)->not->toContain('<h3 className="line-clamp-3 text-sm leading-snug font-bold transition-colors group-hover:text-primary">')
        ->and($component)->toContain('<p className="line-clamp-3 text-sm leading-snug font-bold transition-colors group-hover:text-primary">');
});

it('uses non-heading labels for footer navigation groups', function () {
    $component = file_get_contents(
        resource_path('js/components/layout/footer/FooterLinks.tsx')
    );

    expect($component)->not->toBeFalse()
        ->and($component)->not->toContain('<h4 className="mb-4 flex items-center gap-2 text-xs font-semibold tracking-widest text-foreground/80 uppercase">')
        ->and($component)->toContain('<p className="mb-4 flex items-center gap-2 text-xs font-semibold tracking-widest text-foreground/80 uppercase">');
});

it('uses the lighter detail-book rendering path for performance-sensitive content', function () {
    $resourceDetailPage = file_get_contents(
        resource_path('js/components/kti/KtiDetailPage.tsx')
    );
    $bookDetailPage = file_get_contents(
        resource_path('js/features/books/components/BookDetailPage.tsx')
    );

    expect($resourceDetailPage)->toContain('showBackground?: boolean;')
        ->and($resourceDetailPage)->toContain('deferSecondaryContent?: boolean;')
        ->and($resourceDetailPage)->toContain('contentClassName?: string;')
        ->and($resourceDetailPage)->toContain('secondarySidebar?: ReactNode;')
        ->and($resourceDetailPage)->toContain('{showBackground ? <BackgroundPattern /> : null}')
        ->and($resourceDetailPage)->toContain("contentVisibility: 'auto' as const")
        ->and($resourceDetailPage)->toContain("className={cn('py-10', contentClassName)}")
        ->and($resourceDetailPage)->toContain('const hasSecondarySidebar =')
        ->and($resourceDetailPage)->toContain('secondarySidebar !== undefined && secondarySidebar !== null;')
        ->and($resourceDetailPage)->toContain('className="order-2 md:order-1 md:col-span-4 lg:col-span-3"')
        ->and($resourceDetailPage)->toContain('hasSecondarySidebar &&')
        ->and($resourceDetailPage)->toContain("'xl:sticky xl:top-24'")
        ->and($resourceDetailPage)->toContain('className="order-3 md:order-3 md:col-span-12 lg:col-span-3"')
        ->and($resourceDetailPage)->toContain('className="xl:sticky xl:top-24"')
        ->and($bookDetailPage)->toContain('showBackground={false}')
        ->and($bookDetailPage)->toContain('deferSecondaryContent')
        ->and($bookDetailPage)->toContain('secondarySidebar={')
        ->and($bookDetailPage)->toContain('contentClassName="pt-6 pb-10 sm:pt-8"')
        ->and($bookDetailPage)->toContain('fetchPriority="high"')
        ->and($bookDetailPage)->not->toContain('backgroundImage: `url(${book.coverImageUrl})`');
});

it('keeps book detail covers full while leaving admin cover uploads uncropped', function () {
    $bookDetailPage = file_get_contents(
        resource_path('js/features/books/components/BookDetailPage.tsx')
    );
    $bookForm = file_get_contents(
        app_path('Filament/Resources/Books/Schemas/BookForm.php')
    );
    $bookCoverService = file_get_contents(
        app_path('Services/BookCoverImageService.php')
    );
    $booksTable = file_get_contents(
        app_path('Filament/Resources/Books/Tables/BooksTable.php')
    );

    expect($bookDetailPage)->toContain('object-contain')
        ->and($bookDetailPage)->toContain('max-h-[80vh]')
        ->and($bookDetailPage)->toContain('max-w-[calc(100vw-2rem)]')
        ->and($bookDetailPage)->not->toContain('className="aspect-3/4 w-full object-cover"')
        ->and($bookDetailPage)->not->toContain('border border-white/10')
        ->and($bookDetailPage)->not->toContain('bg-linear-to-br from-white/5 to-transparent')
        ->and($bookForm)->toContain("->imagePreviewHeight('320')")
        ->and($bookForm)->not->toContain("->imageAspectRatio('3:4')")
        ->and($bookForm)->not->toContain('->automaticallyCropImagesToAspectRatio()')
        ->and($bookCoverService)->toContain('->fit(Fit::Max, 1200, 1600)')
        ->and($bookCoverService)->not->toContain('->fit(Fit::Crop, 600, 800)')
        ->and($booksTable)->toContain('->extraImgAttributes([');
});

it('keeps the catalog feedback card on the public book detail page', function () {
    $bookDetailPage = file_get_contents(
        resource_path('js/features/books/components/BookDetailPage.tsx')
    );
    expect($bookDetailPage)->not->toBeFalse()
        ->and($bookDetailPage)->toContain("import { KtiReportCard, KtiReportCardSkeleton } from '@/components/kti/KtiReportCard';")
        ->and($bookDetailPage)->toContain('<KtiReportCard')
        ->and($bookDetailPage)->toContain('catalogType="book"')
        ->and($bookDetailPage)->toContain('catalogLabel="Buku"')
        ->and($bookDetailPage)->toContain('catalogTitle={book.title}')
        ->and($bookDetailPage)->toContain('<KtiReportCardSkeleton />')
        ->and($bookDetailPage)->toContain('secondarySidebar={');
});

it('keeps the book detail metadata sidebar stable across isbn and issn variants', function () {
    $bookDetailPage = file_get_contents(
        resource_path('js/features/books/components/BookDetailPage.tsx')
    );

    expect($bookDetailPage)->not->toBeFalse()
        ->and($bookDetailPage)->toContain('const detailItems = book')
        ->and($bookDetailPage)->toContain("label: 'ISBN / ISSN'")
        ->and($bookDetailPage)->toContain("label: 'Edisi / Volume'")
        ->and($bookDetailPage)->toContain("label: 'Halaman'")
        ->and($bookDetailPage)->toContain('className="px-5 py-4"')
        ->and($bookDetailPage)->not->toContain('className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase"')
        ->and($bookDetailPage)->toContain('{shelfLocations || \'-\'}')
        ->and($bookDetailPage)->toContain('{detailItems.map((item) => (');
});

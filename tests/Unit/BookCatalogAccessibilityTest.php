<?php

it('keeps accessible names on the book catalog filter comboboxes', function () {
    $component = file_get_contents(
        resource_path('js/features/books/components/BookCatalogFilters.tsx')
    );

    expect($component)->not->toBeFalse()
        ->and($component)->toContain('aria-label="Filter kategori buku"')
        ->and($component)->toContain('aria-label="Filter tahun buku"');
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
        resource_path('js/components/catalog/ResourceCatalogHeader.tsx')
    );
    $bookDetailPage = file_get_contents(
        resource_path('js/features/books/components/BookDetailPage.tsx')
    );
    $thesisDetailPage = file_get_contents(
        resource_path('js/features/thesis/components/ThesisDetailPage.tsx')
    );
    $skripsiDetailPage = file_get_contents(
        resource_path('js/features/skripsi/components/SkripsiDetailPage.tsx')
    );
    $internshipReportDetailPage = file_get_contents(
        resource_path('js/features/internship-report/components/InternshipReportDetailPage.tsx')
    );
    $detailSkeleton = file_get_contents(
        resource_path('js/components/resource/ResourceDetailPageSkeleton.tsx')
    );

    expect($catalogHeader)->toContain('pt-24 pb-12 sm:pt-30')
        ->and($catalogHeader)->toContain('className="mb-6"')
        ->and($bookDetailPage)->toContain('pt-24 pb-6 sm:pt-30 sm:pb-8')
        ->and($bookDetailPage)->toContain('className="mb-6"')
        ->and($thesisDetailPage)->toContain('pt-24 pb-12 sm:pt-30')
        ->and($skripsiDetailPage)->toContain('pt-24 pb-12 sm:pt-30')
        ->and($internshipReportDetailPage)->toContain('pt-24 pb-12 sm:pt-30')
        ->and($detailSkeleton)->toContain('pt-24 pb-12 sm:pt-30')
        ->and($detailSkeleton)->toContain('mb-6 flex items-center gap-2');
});

it('shows book card skeletons when mobile progressive pagination starts loading more results', function () {
    $mobileProgressivePagination = file_get_contents(
        resource_path('js/components/catalog/MobileProgressivePagination.tsx')
    );
    $bookCatalogPage = file_get_contents(
        resource_path('js/features/books/components/BookCatalogPage.tsx')
    );

    expect($mobileProgressivePagination)->toContain('loadingFallback?: ReactNode;')
        ->and($mobileProgressivePagination)->toContain('const loadingSkeleton = loadingFallback ?? (')
        ->and($bookCatalogPage)->toContain('loadingFallback={')
        ->and($bookCatalogPage)->toContain('Array.from({ length: 4 })')
        ->and($bookCatalogPage)->toContain("viewMode === 'list' ? 'compact' : 'grid'");
});

it('does not use a skipped heading level for skripsi card titles in the catalog grid', function () {
    $component = file_get_contents(
        resource_path('js/features/skripsi/components/SkripsiCard.tsx')
    );

    expect($component)->not->toBeFalse()
        ->and($component)->not->toContain('<h3 className="line-clamp-3 text-sm leading-snug font-bold transition-colors group-hover:text-primary">')
        ->and($component)->toContain('<p className="line-clamp-3 text-sm leading-snug font-bold transition-colors group-hover:text-primary">');
});

it('does not use a skipped heading level for thesis card titles in the catalog grid', function () {
    $component = file_get_contents(
        resource_path('js/features/thesis/components/ThesisCard.tsx')
    );

    expect($component)->not->toBeFalse()
        ->and($component)->not->toContain('<h3 className="line-clamp-3 text-sm leading-snug font-bold transition-colors group-hover:text-primary">')
        ->and($component)->toContain('<p className="line-clamp-3 text-sm leading-snug font-bold transition-colors group-hover:text-primary">');
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
        resource_path('js/components/layouts/footer/FooterLinks.tsx')
    );

    expect($component)->not->toBeFalse()
        ->and($component)->not->toContain('<h4 className="mb-4 flex items-center gap-2 text-xs font-semibold tracking-widest text-foreground/80 uppercase">')
        ->and($component)->toContain('<p className="mb-4 flex items-center gap-2 text-xs font-semibold tracking-widest text-foreground/80 uppercase">');
});

it('uses the lighter detail-book rendering path for performance-sensitive content', function () {
    $resourceDetailPage = file_get_contents(
        resource_path('js/components/resource/ResourceDetailPage.tsx')
    );
    $bookDetailPage = file_get_contents(
        resource_path('js/features/books/components/BookDetailPage.tsx')
    );

    expect($resourceDetailPage)->toContain('showBackground?: boolean;')
        ->and($resourceDetailPage)->toContain('deferSecondaryContent?: boolean;')
        ->and($resourceDetailPage)->toContain('contentClassName?: string;')
        ->and($resourceDetailPage)->toContain('{showBackground ? <BackgroundPattern /> : null}')
        ->and($resourceDetailPage)->toContain("contentVisibility: 'auto' as const")
        ->and($resourceDetailPage)->toContain("className={cn('py-10', contentClassName)}")
        ->and($bookDetailPage)->toContain('showBackground={false}')
        ->and($bookDetailPage)->toContain('deferSecondaryContent')
        ->and($bookDetailPage)->toContain('contentClassName="pt-2 pb-10 sm:pt-3"')
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
        ->and($bookDetailPage)->toContain('max-h-[28rem] w-auto max-w-full object-contain')
        ->and($bookDetailPage)->toContain('className="flex min-h-[18rem] items-center justify-center sm:min-h-[22rem]"')
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

# Changelog

## [1.12.2](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.12.1...v1.12.2) (2026-07-08)


### Bug Fixes

* **ui:** optimize global search query matching, autocomplete suggestions and display ([#23](https://github.com/ghiyatsa/ruangbacainformatika/issues/23)) ([bded533](https://github.com/ghiyatsa/ruangbacainformatika/commit/bded5332fc0a1812f52461b8bd602746924bc7bd))

## [1.12.1](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.12.0...v1.12.1) (2026-07-07)


### Bug Fixes

* **ui:** trust all proxies to resolve absolute URL scheme correctly ([278fc6e](https://github.com/ghiyatsa/ruangbacainformatika/commit/278fc6e0c45f0ed9680dc862bb384ff6107b3e44))

## [1.12.0](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.11.1...v1.12.0) (2026-07-07)


### Features

* restore member key settings page and fix Filament user role update ([5097e6f](https://github.com/ghiyatsa/ruangbacainformatika/commit/5097e6f0c9cb09d17b3c64dd1850988eb82fbc2b))


### Bug Fixes

* **ui:** resolve eslint errors and warnings on settings and search ([32d0f95](https://github.com/ghiyatsa/ruangbacainformatika/commit/32d0f950599779909e5ed673a58fc0716d3fd12d))

## [1.11.1](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.11.0...v1.11.1) (2026-07-05)


### Bug Fixes

* optimize similarity sync timeout and chunk size for HF space ([4eacef8](https://github.com/ghiyatsa/ruangbacainformatika/commit/4eacef85582417237ade7c53d4d8fc8c83ae0a8c))

## [1.11.0](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.10.0...v1.11.0) (2026-07-04)


### Features

* **auth:** implement dedicated login page with google identity services ([282fdaa](https://github.com/ghiyatsa/ruangbacainformatika/commit/282fdaa8984a2f3ffae8e971b664092240818b14))
* **similarity:** implement polymorphic sync for Skripsi and Laporan KP & resolve welcome page SEO issues ([ff22eab](https://github.com/ghiyatsa/ruangbacainformatika/commit/ff22eabc4b78d09ea62192f6f3913739cbdf2c0a))

## [1.10.0](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.9.0...v1.10.0) (2026-07-04)


### Features

* **ui,catalog:** revise similarity page UI, fix robots.txt crawl, and align book catalog mobile filters skeleton ([2325aef](https://github.com/ghiyatsa/ruangbacainformatika/commit/2325aef2c6ad2be883f416f207fc3404b926eabd))


### Bug Fixes

* **eslint:** resolve react hook rules & padding stylistic errors in detail pages ([7426730](https://github.com/ghiyatsa/ruangbacainformatika/commit/7426730054fd69d091ee691421d2773a4860232e))

## [1.9.0](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.8.0...v1.9.0) (2026-06-26)


### Features

* **pwa,blog:** implement PWA support & fix blog OG image thumbnail mapping ([4809fa9](https://github.com/ghiyatsa/ruangbacainformatika/commit/4809fa91ab17e669890f4d34b1bdc438586df784))


### Bug Fixes

* **ui:** increase z-index and remove border from preview watermark badge ([56dba0f](https://github.com/ghiyatsa/ruangbacainformatika/commit/56dba0f2ca491b2b4f7ec426b3c46b4281e3773a))

## [1.8.0](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.7.0...v1.8.0) (2026-06-22)


### Features

* **ui:** implement global search and UI optimizations ([#14](https://github.com/ghiyatsa/ruangbacainformatika/issues/14)) ([b14153e](https://github.com/ghiyatsa/ruangbacainformatika/commit/b14153e8816060d331ddb898f0e8b904781ceb07))

## [1.7.0](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.6.0...v1.7.0) (2026-06-20)


### Features

* **settings:** add per post comment toggle and dashboard review alerts ([d193556](https://github.com/ghiyatsa/ruangbacainformatika/commit/d193556d62c23f603734e3e39f03c8d26ed6a68b))
* **ui:** display full-width empty state card on blog index ([36a321c](https://github.com/ghiyatsa/ruangbacainformatika/commit/36a321c20dd9b939ed5d7a73476e5e1b8818dc21))
* **ui:** implement deferred loading and skeletons for blog list and detail ([aa92925](https://github.com/ghiyatsa/ruangbacainformatika/commit/aa92925b7950a0c4e8910008cd375c665957653c))

## [1.6.0](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.5.0...v1.6.0) (2026-06-19)


### Features

* **catalog:** support bookmarks and align layout for detail pages ([f7ec282](https://github.com/ghiyatsa/ruangbacainformatika/commit/f7ec282fb935740bb9a39f2a75d9b6e228639f11))
* **ui:** implement responsive blog feature with tags and categories ([d8ec756](https://github.com/ghiyatsa/ruangbacainformatika/commit/d8ec7561258ebe21cb5c1cd1eda49fad9572f682))


### Bug Fixes

* **ui:** pass fragment to Deferred fallback to prevent runtime exception ([4e09c4b](https://github.com/ghiyatsa/ruangbacainformatika/commit/4e09c4b83e8236197b9776e23fc36280ac90b327))
* **ui:** restore notice skeleton and align catalog breadcrumbs with details ([425f9d2](https://github.com/ghiyatsa/ruangbacainformatika/commit/425f9d253bf9d637e40091896f32f8df6ece4b32))

## [1.5.0](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.4.0...v1.5.0) (2026-06-19)


### Features

* **ui:** add skeleton loading for global content notice and notifications dropdown ([dcfa42f](https://github.com/ghiyatsa/ruangbacainformatika/commit/dcfa42f45f5da6148a0dd7f23763f1793bdbd992))


### Bug Fixes

* **admin:** drop unused columns from posts table ([351c888](https://github.com/ghiyatsa/ruangbacainformatika/commit/351c888e702a7b6435584efc596c68dd4aaab5cf))

## [1.4.0](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.3.0...v1.4.0) (2026-06-17)


### Features

* **blog:** implement approval workflow and resources for dashboard and admin panels ([5e5981c](https://github.com/ghiyatsa/ruangbacainformatika/commit/5e5981c5670b2fd8f3cadc1eacc7d26cb9c756c9))
* implement blog system, refine book form layout, and update book identifier constraints ([23f65b6](https://github.com/ghiyatsa/ruangbacainformatika/commit/23f65b605496817d96fbededc7a06ac03bc2f93f))
* **panel:** create dashboard panel for members ([41e8d5a](https://github.com/ghiyatsa/ruangbacainformatika/commit/41e8d5a250aa226a5a5ac70a12a7ee8f2da05d51))
* **seo:** add google search console verification and dynamic sitemap ([305efed](https://github.com/ghiyatsa/ruangbacainformatika/commit/305efed6111e446810e3f073710eb450b0680bdb))


### Bug Fixes

* **catalog:** fix pagination skeleton bug and adjust transition to auto infinite scroll ([e2e0247](https://github.com/ghiyatsa/ruangbacainformatika/commit/e2e0247039251eb4f3cef464e898701beb75f338))
* **catalog:** remove backdrop blur from dialog overlay to prevent mobile lag ([46c9fcd](https://github.com/ghiyatsa/ruangbacainformatika/commit/46c9fcd3f99a676cbf9069e6bdf0d535a1b0fb90))
* **catalog:** resolve duplicate cover and enable expand cover on mobile ([b48d9db](https://github.com/ghiyatsa/ruangbacainformatika/commit/b48d9dbfdc9c5bb466aac17b9fb8165b0117620b))


### Performance Improvements

* **catalog:** code-split filters for academic-works and internship-reports ([99e6925](https://github.com/ghiyatsa/ruangbacainformatika/commit/99e6925a851bc383a1490353b0d46f05cfcf5634))
* **catalog:** implement viewport-based lazy loading for home page sections ([ef488ff](https://github.com/ghiyatsa/ruangbacainformatika/commit/ef488ff5e0485ada579b24bf45898155695deeda))

## [1.3.0](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.2.0...v1.3.0) (2026-06-13)


### Features

* add ActivityLogService and SimilarityFullSyncDispatcher with corresponding tests and Filament integration settings page ([329e0d2](https://github.com/ghiyatsa/ruangbacainformatika/commit/329e0d21f4771540c50ef0164d0d0da116bd2b44))
* add BookForm schema for Filament resource with sections for metadata, publication, media, and relations ([522ab0b](https://github.com/ghiyatsa/ruangbacainformatika/commit/522ab0b594b020c178b0aa5375f9a3b0df321954))
* add brand logo component and configure application icons and manifest ([36fdc0e](https://github.com/ghiyatsa/ruangbacainformatika/commit/36fdc0e0e07c918991f9b3eb48946b1744ee5d77))
* add bulk synchronization service and command for Similarity API integration ([dd62e5e](https://github.com/ghiyatsa/ruangbacainformatika/commit/dd62e5e5046a5947f25c3fb57115a26009c5a3a9))
* add full-text indexes to skripsis, theses, and internship_reports tables ([0e39ab9](https://github.com/ghiyatsa/ruangbacainformatika/commit/0e39ab90e6acff3bf6134dc81f48504f3310c9cd))
* add loan draft and loan draft item factories, migrations, and QR scanner component ([ced8550](https://github.com/ghiyatsa/ruangbacainformatika/commit/ced85505dba2ce67b9e230ac4467f2b9b85ed398))
* add network scope to kiosk devices and implement app settings seeder ([4ef73c7](https://github.com/ghiyatsa/ruangbacainformatika/commit/4ef73c7ebfbb5edbef7ac5bbf0bccfcbd5c038b7))
* add open graph social sharing image to public assets ([2c5c8df](https://github.com/ghiyatsa/ruangbacainformatika/commit/2c5c8df0a5bd5011bd33f865e033e10a7ac5cafb))
* add OpenGraphImage support class for dynamic social media card generation ([ef24c15](https://github.com/ghiyatsa/ruangbacainformatika/commit/ef24c1532372187e13091bbeef4e3e8e3b859562))
* add similarity sync dispatch service and Filament integration settings page ([6dc2b07](https://github.com/ghiyatsa/ruangbacainformatika/commit/6dc2b07cf41f8d36e7561e478010f77a47090eb7))
* add SimilarityApiService for API integration and implement CI workflow for automated testing ([828eee9](https://github.com/ghiyatsa/ruangbacainformatika/commit/828eee97d7ebf3403d3a53b5bcfb5b862fb3c37e))
* add SuperAdminSeeder to initialize super admin user and role ([c53c715](https://github.com/ghiyatsa/ruangbacainformatika/commit/c53c7152f80b71dbb4ea437fceb08b2cf82303f3))
* add view count to catalog reports and implement bookmark functionality ([beb9ea7](https://github.com/ghiyatsa/ruangbacainformatika/commit/beb9ea773bb3071984da83ade02cabac9d2d2501))
* add welcome feature components, kbd UI, and layout utilities to enhance home page catalog and branding ([1e88fea](https://github.com/ghiyatsa/ruangbacainformatika/commit/1e88fea6d5d2209ada230fba15f1b4e83c3e5a63))
* **catalog:** add issn column to book importer ([7b473d8](https://github.com/ghiyatsa/ruangbacainformatika/commit/7b473d81cb27e0e6819373bdc832a30b7322e36f))
* **catalog:** add most borrowed books and popular category shelves to landing page ([9776d54](https://github.com/ghiyatsa/ruangbacainformatika/commit/9776d543109d9a35cf508e06b31a206acfe8c529))
* **catalog:** add searchable filters and skeleton to resource catalog ([1382480](https://github.com/ghiyatsa/ruangbacainformatika/commit/138248050a6e7dd6d065a2ea289ff314f6e5bd95))
* configure AppServiceProvider and initialize Pest test environment ([74c7eea](https://github.com/ghiyatsa/ruangbacainformatika/commit/74c7eead3bf9ad3eea8140143a71afef14e2f033))
* configure filesystem disks and define public book storage symlink targets ([807ac57](https://github.com/ghiyatsa/ruangbacainformatika/commit/807ac571db96ef51813b03794055c4d16908ef62))
* configure Railway deployment settings and update Nixpacks build environment requirements ([3ef1b27](https://github.com/ghiyatsa/ruangbacainformatika/commit/3ef1b274b199b61a59f6de8e4d414cf142e60ad7))
* create base application layout with dark mode support and Open Graph meta tags ([c56ffc1](https://github.com/ghiyatsa/ruangbacainformatika/commit/c56ffc12af0a04bdc32ef6d74a98a782e8fcd8e2))
* create BookResource to standardize book model API responses ([f2bcdf8](https://github.com/ghiyatsa/ruangbacainformatika/commit/f2bcdf8a02493d5b1d3125cf4e5470940322ef4d))
* create PublicInfoCard and PublicPageSection components for layout ([b936c0d](https://github.com/ghiyatsa/ruangbacainformatika/commit/b936c0d43a218169a252a48b9186906a5095cf28))
* implement animated Hero section and SimilarityController for welcome page ([ab8b7d5](https://github.com/ghiyatsa/ruangbacainformatika/commit/ab8b7d52548fab698db62115e37cbb06fa30ee9e))
* implement authorization policies for core application models ([5138a2a](https://github.com/ghiyatsa/ruangbacainformatika/commit/5138a2ac10f109a6a0477d5351797f31c4909cd7))
* implement automated email reminders for loans due tomorrow ([b65e686](https://github.com/ghiyatsa/ruangbacainformatika/commit/b65e686989ab125ca0b4c863abd7c264db2d4e1a))
* implement base access control policies for application models ([9587b78](https://github.com/ghiyatsa/ruangbacainformatika/commit/9587b784ac374138b054f8c33cb39e71810f56ff))
* implement base model access policies for application entities ([a2624ad](https://github.com/ghiyatsa/ruangbacainformatika/commit/a2624ad1af1aa2ca03b1f21c371bd2a19c7e43a6))
* implement book detail page component with availability status and metadata display ([7125d85](https://github.com/ghiyatsa/ruangbacainformatika/commit/7125d858d2f51a7908984ed8cdaf02d30427bbcd))
* implement book detail page component with availability status and metadata display ([c993029](https://github.com/ghiyatsa/ruangbacainformatika/commit/c993029a46c28f4766a3e6974a8ff3a2a7680875))
* implement book image processing service, data import functionality, and structured Filament forms and tables ([72eec52](https://github.com/ghiyatsa/ruangbacainformatika/commit/72eec52d179c91a41b8aaac78a01a7781c39ad8b))
* implement book import functionality with ISBN normalization and support for related data models. ([a2ad81c](https://github.com/ghiyatsa/ruangbacainformatika/commit/a2ad81c783536cda4c114788d9aa603e213fc47d))
* implement catalog pages for books and skripsi including filtering, search, and detail views ([a5366af](https://github.com/ghiyatsa/ruangbacainformatika/commit/a5366af964e4a094ac766863acacc2eb346be32b))
* implement catalog reports and contact messaging systems with Filament admin resources and UI components ([fdf97c5](https://github.com/ghiyatsa/ruangbacainformatika/commit/fdf97c5f1a2e35d26c502d1c06845b7b7cd6f63e))
* implement catalog system with enhanced book display, search, and filtration components ([f3d6608](https://github.com/ghiyatsa/ruangbacainformatika/commit/f3d66088813bc36c6c89e68faeba3480ecef5a7c))
* implement comprehensive authorization policies for core system models ([f443b02](https://github.com/ghiyatsa/ruangbacainformatika/commit/f443b0268e0b6471c505289c2dcef97739c8d4c5))
* implement comprehensive catalog features, WhatsApp notification system, and React-based UI architecture with optimized build configuration. ([2537724](https://github.com/ghiyatsa/ruangbacainformatika/commit/2537724b755685a963a435adb6646134d0740d4d))
* implement comprehensive Filament dashboard widgets and resource structures for library operations ([aef5b65](https://github.com/ghiyatsa/ruangbacainformatika/commit/aef5b6549d29be224961b0f038fb6dd69ec24fc6))
* implement comprehensive kiosk flow, authentication system, and developer documentation skills ([3c620a1](https://github.com/ghiyatsa/ruangbacainformatika/commit/3c620a1f1384e731a6afe64d0b4be607e620001b))
* implement comprehensive kiosk system, library circulation features, and WhatsApp-based authentication with user profile management ([d00c4a9](https://github.com/ghiyatsa/ruangbacainformatika/commit/d00c4a98c1fbef71042655c5d76090a1d59cd276))
* implement comprehensive landing page, catalog views, and layout architecture ([fb5f000](https://github.com/ghiyatsa/ruangbacainformatika/commit/fb5f000d3e60faf5238f6d44dd067f637665178a))
* implement comprehensive security headers and modernize frontend catalog components ([600b213](https://github.com/ghiyatsa/ruangbacainformatika/commit/600b213bf319ae5a0ddb0a527185621852a29502))
* implement comprehensive user authentication, profile management, and modular Filament resource administration system ([aaa44fe](https://github.com/ghiyatsa/ruangbacainformatika/commit/aaa44fee9a888b3ee8c0d2275ca8fb517e283fba))
* implement core book browsing, loan management, and user authentication features with associated UI components and Filament resources. ([d1481f9](https://github.com/ghiyatsa/ruangbacainformatika/commit/d1481f9311a5ff0a210813d408c3a7bd4764d384))
* implement core feature set including auth flows, catalog UI, kiosk functionality, and admin panel testing suite ([33ce62a](https://github.com/ghiyatsa/ruangbacainformatika/commit/33ce62a239a0ca7e5bf7e46e652f151958ea8d15))
* implement core library catalog features, user settings, and application layout components ([33fe9e9](https://github.com/ghiyatsa/ruangbacainformatika/commit/33fe9e952d5251dad0bc194350d14654bc01e19a))
* implement core resource library UI components, skeletons, and layout architecture ([7d8d1bf](https://github.com/ghiyatsa/ruangbacainformatika/commit/7d8d1bf769d40299a15b346de873fd2da05aefb8))
* implement core resource management modules, similarity synchronization, and refined frontend UI components ([d6b41a2](https://github.com/ghiyatsa/ruangbacainformatika/commit/d6b41a2617d54c302dcbb1460e68434719974b10))
* implement dynamic Open Graph image generation and add loan activity chart widget ([1c35823](https://github.com/ghiyatsa/ruangbacainformatika/commit/1c35823055ab6e307c4a82d64a2d7fb4e18e284e))
* implement dynamic Open Graph image generation for site and catalog pages ([566a894](https://github.com/ghiyatsa/ruangbacainformatika/commit/566a8949b368f25460b7d26910d3671e1e7fa131))
* implement dynamic OpenGraph image generation service with associated tests and assets ([2141891](https://github.com/ghiyatsa/ruangbacainformatika/commit/21418916927353ad577980cee84d6056baa522bd))
* implement Filament tables for categories, authors, and publishers, and configure filesystem disk for book cover storage. ([425c592](https://github.com/ghiyatsa/ruangbacainformatika/commit/425c5921d5208ddb158a26a56dcae6cea1579eab))
* implement full-stack frontend catalog, search, and user settings features ([c47c2e3](https://github.com/ghiyatsa/ruangbacainformatika/commit/c47c2e383132c10f37955ae40e1b509eb0741447))
* implement full-stack kiosk circulation system with return draft management and library services ([81b5bee](https://github.com/ghiyatsa/ruangbacainformatika/commit/81b5bee1c64f5042a90e70f970fda01d19272a6f))
* implement full-stack kiosk system, catalog features, user settings dashboard, and similarity search module ([36ed9d4](https://github.com/ghiyatsa/ruangbacainformatika/commit/36ed9d4a4563877a1835a0962babec784e370e97))
* implement full-stack library management system with Filament resources, Inertia.js frontend, and automated testing ([1fe9cb0](https://github.com/ghiyatsa/ruangbacainformatika/commit/1fe9cb010ce52c3c67d6b6a71ed7509e06913834))
* implement full-stack WhatsApp notification system and reorganize Filament resource schemas and tables ([a91e047](https://github.com/ghiyatsa/ruangbacainformatika/commit/a91e0479c24bbe24e9f45f528a13901c20103f33))
* implement Google One Tap sign-in and standard OAuth authentication flow ([4373769](https://github.com/ghiyatsa/ruangbacainformatika/commit/43737690a5019cd7382ed3fb0f624f99546ed041))
* implement granular model authorization policies and add VelocityScroll UI component ([94bd58e](https://github.com/ghiyatsa/ruangbacainformatika/commit/94bd58e0031c45c641ae27598eee0aff218cee2a))
* implement home page catalog view with ISSN, borrowability, and book availability logic. ([6606b2d](https://github.com/ghiyatsa/ruangbacainformatika/commit/6606b2dd7cc4fb70d3f7dc0643c92c53ba425d31))
* implement home page dashboard with catalog stats, deferred category loading, and site-wide notifications ([474cff1](https://github.com/ghiyatsa/ruangbacainformatika/commit/474cff1b1cf7b6168cebf046105629ad8ec883e9))
* implement integration settings page and similarity check controller with caching and validation logic ([5cc3423](https://github.com/ghiyatsa/ruangbacainformatika/commit/5cc34231cbc227226b12c5ad86f48432fdb00d1f))
* implement internship report management system and add overdue loan tracking widget ([a129894](https://github.com/ghiyatsa/ruangbacainformatika/commit/a1298943ef7cf2c97cd0a25f53367e57a2753b02))
* implement kiosk device management, catalog filtering, and multi-factor authentication systems ([5574a0f](https://github.com/ghiyatsa/ruangbacainformatika/commit/5574a0f849580cf6bc37a33346cec4e88eb6af89))
* implement kiosk interface for book borrowing and returning functionality ([71dc7f8](https://github.com/ghiyatsa/ruangbacainformatika/commit/71dc7f89caf5c25165dd35b3a1284a4c76e0ba35))
* implement kiosk interface, general settings cluster, and categorized book browsing with filtering. ([2c5b44e](https://github.com/ghiyatsa/ruangbacainformatika/commit/2c5b44ef29ca2af3ac27997524a6ace9fa479259))
* implement kiosk loan management system, email verification OTP flow, and shared Inertia request middleware ([ba66599](https://github.com/ghiyatsa/ruangbacainformatika/commit/ba66599278fb36009b745ff1f3cb6e9c1da51465))
* implement kiosk loan service, PIN access management, and automated return reminder system with comprehensive feature tests ([7c14b1b](https://github.com/ghiyatsa/ruangbacainformatika/commit/7c14b1b93235bb38dce87058b7a096bdefdfd6d7))
* implement kiosk PIN management, resource policies, models, and administrative scaffolding ([1df2e60](https://github.com/ghiyatsa/ruangbacainformatika/commit/1df2e602e267f5f021e2ee73b7541298fa2b1286))
* implement kiosk verification, member key renaming, and book page adjustments ([#5](https://github.com/ghiyatsa/ruangbacainformatika/issues/5)) ([5e94a85](https://github.com/ghiyatsa/ruangbacainformatika/commit/5e94a85b370f1ebd841562717f843aa523ca3eb8))
* implement library kiosk system including member registration, book search, and visitor logging functionality ([48df23a](https://github.com/ghiyatsa/ruangbacainformatika/commit/48df23a81945a93ee7c910f2ff1422acfd861d29))
* implement modular catalog system and similarity synchronization infrastructure ([e7d363e](https://github.com/ghiyatsa/ruangbacainformatika/commit/e7d363ef60ae24606f37e408dc7b19dbd92d25a4))
* implement modular Filament table and form schemas for user management ([7b9d32c](https://github.com/ghiyatsa/ruangbacainformatika/commit/7b9d32cf72e18f08b01e9e7168850a468d2729e4))
* implement OpenGraphImage generator service and controller for dynamic social preview images ([c2155c6](https://github.com/ghiyatsa/ruangbacainformatika/commit/c2155c6581254d50967ca8a0fb8c02496728c638))
* implement profile management system with validation and verification workflows ([814d6f8](https://github.com/ghiyatsa/ruangbacainformatika/commit/814d6f878c6b3426201ee0048c195ddaede6ae85))
* implement profile onboarding flow and add Google authentication feature tests ([a1b9fa2](https://github.com/ghiyatsa/ruangbacainformatika/commit/a1b9fa252ad95922653e853306f81dff254d686a))
* implement queued email notifications for loan receipts and OTP verification, and update build scripts for PHP environment compatibility ([6f2ff9d](https://github.com/ghiyatsa/ruangbacainformatika/commit/6f2ff9dcb2737b9a8de356f88fc8d309e092f348))
* implement resource access authorization policies for all core system models ([f518d4a](https://github.com/ghiyatsa/ruangbacainformatika/commit/f518d4a20a68e35560a0aebedcda8349323b9edd))
* implement resource policies and add admin panel feature tests ([a11caf5](https://github.com/ghiyatsa/ruangbacainformatika/commit/a11caf513d732e70177830858e870ee44cf611b3))
* implement resource policies and add ShieldSeeder for role-based access control ([ca4883b](https://github.com/ghiyatsa/ruangbacainformatika/commit/ca4883bb439e0b6524bbabf9505e106106105d8b))
* implement similarity sync management system and add various Filament admin widgets ([f1b610c](https://github.com/ghiyatsa/ruangbacainformatika/commit/f1b610c77d61176389f3fc8b59592125e41b5d9b))
* implement similarity sync service with queue management and extend user profile validation ([60e859e](https://github.com/ghiyatsa/ruangbacainformatika/commit/60e859e0c5c476d4f3e427b294b61e18973dfd0e))
* implement skripsi catalog and search functionality with new UI components ([43ef9aa](https://github.com/ghiyatsa/ruangbacainformatika/commit/43ef9aa40e0d04d4fab89356e900618498cecb8a))
* implement Skripsi resource management and catalog browse features with new UI components and settings pages ([93170d4](https://github.com/ghiyatsa/ruangbacainformatika/commit/93170d423db0dfc6e140ba233898b128484db6b4))
* implement static pages, activity logging, and site configuration with updated seeders and frontend components ([f32d404](https://github.com/ghiyatsa/ruangbacainformatika/commit/f32d404b31fe20ca784b3514e3f486a98975e150))
* implement static pages, book management utilities, and interactive team profile page ([09e839d](https://github.com/ghiyatsa/ruangbacainformatika/commit/09e839d840da829ad6099c23f63090e3a32198de))
* implement SuperAdminSeeder and update deployment commands to seed initial admin user ([13e1d3f](https://github.com/ghiyatsa/ruangbacainformatika/commit/13e1d3f4fbda791dd740f235d8cfdc95990e3b3e))
* implement thesis resource management, catalog pages, and developer skill documentation ([c9b9426](https://github.com/ghiyatsa/ruangbacainformatika/commit/c9b9426af1ac4cda56501f5f31267a8577f803f6))
* implement UI components, skeleton loaders, and appearance hook for layout and theme management ([92d422a](https://github.com/ghiyatsa/ruangbacainformatika/commit/92d422a94dfe683fc201d95e71885a0409c126fb))
* improve accessibility and performance by enhancing catalog pagination, optimizing resource detail rendering, and introducing OG image support. ([72ce27b](https://github.com/ghiyatsa/ruangbacainformatika/commit/72ce27b6f9706f79e3f4b8dd75f9932af320785b))
* initialize application providers and configure custom middleware, routing, and security defaults ([c470e18](https://github.com/ghiyatsa/ruangbacainformatika/commit/c470e186abe7aee24bacadb5a629a48105e0a8ab))
* initialize default filesystem configuration with local, public, and s3 disks ([d71809b](https://github.com/ghiyatsa/ruangbacainformatika/commit/d71809b2de07ed13c6494b4cd672d28d10738dfc))
* initialize project structure with Filament resources, React-based frontend scaffolding, and development workflows. ([3bda3ff](https://github.com/ghiyatsa/ruangbacainformatika/commit/3bda3ff85b6388b320184e742fc24dafbb3b8777))
* integrate Resend for email services and implement custom user registration logic with feature tests ([8de5dc0](https://github.com/ghiyatsa/ruangbacainformatika/commit/8de5dc0074b91424780bf7c76403d954512ac737))
* **kiosk:** implement operating hours, idle timeouts, and member qr verificationFeature/git automation ([#3](https://github.com/ghiyatsa/ruangbacainformatika/issues/3)) ([6b46b6f](https://github.com/ghiyatsa/ruangbacainformatika/commit/6b46b6fb017567a45b96655d2477efd7dc255762))
* **kiosk:** implement operating hours, session timeouts, and member qr verification ([c4c9821](https://github.com/ghiyatsa/ruangbacainformatika/commit/c4c9821bad92f4ed5dbb896c7ccd5cf78a3b3e28))
* **kiosk:** require member key verification and align session timeouts with operating hours ([7bfe0eb](https://github.com/ghiyatsa/ruangbacainformatika/commit/7bfe0eb8524d728cd17759294f246e5c4ccaefbf))
* scaffold application layout, loan request system, and detail pages with notice component support ([8e96c44](https://github.com/ghiyatsa/ruangbacainformatika/commit/8e96c44899f121fb2c7a2b41f33ff63ebf879461))
* scaffold core frontend UI components, page layouts, and resource detail features ([275d905](https://github.com/ghiyatsa/ruangbacainformatika/commit/275d9056ffeeaaa1a5696f9717da3c6781c9140d))
* scaffold core library features including catalog management, similarity engine, loan processing, and Filament admin infrastructure ([925b543](https://github.com/ghiyatsa/ruangbacainformatika/commit/925b543da1a6a451b9af2b3ad06103bb3149f31c))
* update tooltip position in Skripsi and Thesis cards to top ([b936c0d](https://github.com/ghiyatsa/ruangbacainformatika/commit/b936c0d43a218169a252a48b9186906a5095cf28))


### Bug Fixes

* add railpack.json with required PHP extensions (intl, zip, exif) ([321e9bd](https://github.com/ghiyatsa/ruangbacainformatika/commit/321e9bdddcd87fdc514c1ee8fbabe7ac4f321569))
* **catalog:** resolve homepage query compatibility with sqlite ([e5d8d33](https://github.com/ghiyatsa/ruangbacainformatika/commit/e5d8d3393fb51b89ac6f6e78b661ca32d416f40f))
* ensure tsconfig.json ends with a newline ([b936c0d](https://github.com/ghiyatsa/ruangbacainformatika/commit/b936c0d43a218169a252a48b9186906a5095cf28))
* format view count in Skripsi and Thesis detail pages ([b936c0d](https://github.com/ghiyatsa/ruangbacainformatika/commit/b936c0d43a218169a252a48b9186906a5095cf28))
* make borrowing restriction scope compatible with sqlite ([efa47ad](https://github.com/ghiyatsa/ruangbacainformatika/commit/efa47adbe25e804f8e41527676bf528092937b83))
* resolve undefined method getDriverName static analysis warning ([e7fa1fa](https://github.com/ghiyatsa/ruangbacainformatika/commit/e7fa1fa40d762453b487c4b4d7ec6505fccec337))
* **ui:** adjust global dialog and backdrop z-index layering ([e221341](https://github.com/ghiyatsa/ruangbacainformatika/commit/e22134185bf87f205d90a7703fea016f9aa45d70))
* update TypeScript configuration to ignore deprecations for version 5.0 and refactor imports in authentication components ([a559f6f](https://github.com/ghiyatsa/ruangbacainformatika/commit/a559f6f9b67d0ac38379c1fe6c97b9c53e8fb3f0))

## [1.2.0](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.1.0...v1.2.0) (2026-06-12)


### Features

* implement kiosk verification, member key renaming, and book page adjustments ([#5](https://github.com/ghiyatsa/ruangbacainformatika/issues/5)) ([5e94a85](https://github.com/ghiyatsa/ruangbacainformatika/commit/5e94a85b370f1ebd841562717f843aa523ca3eb8))
* **kiosk:** require member key verification and align session timeouts with operating hours ([7bfe0eb](https://github.com/ghiyatsa/ruangbacainformatika/commit/7bfe0eb8524d728cd17759294f246e5c4ccaefbf))

## [1.2.0](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.1.0...v1.2.0) (2026-06-12)


### Features

* implement kiosk verification, member key renaming, and book page adjustments ([#5](https://github.com/ghiyatsa/ruangbacainformatika/issues/5)) ([5e94a85](https://github.com/ghiyatsa/ruangbacainformatika/commit/5e94a85b370f1ebd841562717f843aa523ca3eb8))

## [1.1.0](https://github.com/ghiyatsa/ruangbacainformatika/compare/v1.0.0...v1.1.0) (2026-06-12)


### Features

* **kiosk:** implement operating hours, idle timeouts, and member qr verificationFeature/git automation ([#3](https://github.com/ghiyatsa/ruangbacainformatika/issues/3)) ([6b46b6f](https://github.com/ghiyatsa/ruangbacainformatika/commit/6b46b6fb017567a45b96655d2477efd7dc255762))

## 1.0.0 (2026-06-06)


### Features

* add ActivityLogService and SimilarityFullSyncDispatcher with corresponding tests and Filament integration settings page ([329e0d2](https://github.com/ghiyatsa/ruangbacainformatika/commit/329e0d21f4771540c50ef0164d0d0da116bd2b44))
* add BookForm schema for Filament resource with sections for metadata, publication, media, and relations ([522ab0b](https://github.com/ghiyatsa/ruangbacainformatika/commit/522ab0b594b020c178b0aa5375f9a3b0df321954))
* add brand logo component and configure application icons and manifest ([36fdc0e](https://github.com/ghiyatsa/ruangbacainformatika/commit/36fdc0e0e07c918991f9b3eb48946b1744ee5d77))
* add bulk synchronization service and command for Similarity API integration ([dd62e5e](https://github.com/ghiyatsa/ruangbacainformatika/commit/dd62e5e5046a5947f25c3fb57115a26009c5a3a9))
* add full-text indexes to skripsis, theses, and internship_reports tables ([0e39ab9](https://github.com/ghiyatsa/ruangbacainformatika/commit/0e39ab90e6acff3bf6134dc81f48504f3310c9cd))
* add loan draft and loan draft item factories, migrations, and QR scanner component ([ced8550](https://github.com/ghiyatsa/ruangbacainformatika/commit/ced85505dba2ce67b9e230ac4467f2b9b85ed398))
* add network scope to kiosk devices and implement app settings seeder ([4ef73c7](https://github.com/ghiyatsa/ruangbacainformatika/commit/4ef73c7ebfbb5edbef7ac5bbf0bccfcbd5c038b7))
* add open graph social sharing image to public assets ([2c5c8df](https://github.com/ghiyatsa/ruangbacainformatika/commit/2c5c8df0a5bd5011bd33f865e033e10a7ac5cafb))
* add OpenGraphImage support class for dynamic social media card generation ([ef24c15](https://github.com/ghiyatsa/ruangbacainformatika/commit/ef24c1532372187e13091bbeef4e3e8e3b859562))
* add similarity sync dispatch service and Filament integration settings page ([6dc2b07](https://github.com/ghiyatsa/ruangbacainformatika/commit/6dc2b07cf41f8d36e7561e478010f77a47090eb7))
* add SimilarityApiService for API integration and implement CI workflow for automated testing ([828eee9](https://github.com/ghiyatsa/ruangbacainformatika/commit/828eee97d7ebf3403d3a53b5bcfb5b862fb3c37e))
* add SuperAdminSeeder to initialize super admin user and role ([c53c715](https://github.com/ghiyatsa/ruangbacainformatika/commit/c53c7152f80b71dbb4ea437fceb08b2cf82303f3))
* add view count to catalog reports and implement bookmark functionality ([beb9ea7](https://github.com/ghiyatsa/ruangbacainformatika/commit/beb9ea773bb3071984da83ade02cabac9d2d2501))
* add welcome feature components, kbd UI, and layout utilities to enhance home page catalog and branding ([1e88fea](https://github.com/ghiyatsa/ruangbacainformatika/commit/1e88fea6d5d2209ada230fba15f1b4e83c3e5a63))
* configure AppServiceProvider and initialize Pest test environment ([74c7eea](https://github.com/ghiyatsa/ruangbacainformatika/commit/74c7eead3bf9ad3eea8140143a71afef14e2f033))
* configure filesystem disks and define public book storage symlink targets ([807ac57](https://github.com/ghiyatsa/ruangbacainformatika/commit/807ac571db96ef51813b03794055c4d16908ef62))
* configure Railway deployment settings and update Nixpacks build environment requirements ([3ef1b27](https://github.com/ghiyatsa/ruangbacainformatika/commit/3ef1b274b199b61a59f6de8e4d414cf142e60ad7))
* create base application layout with dark mode support and Open Graph meta tags ([c56ffc1](https://github.com/ghiyatsa/ruangbacainformatika/commit/c56ffc12af0a04bdc32ef6d74a98a782e8fcd8e2))
* create BookResource to standardize book model API responses ([f2bcdf8](https://github.com/ghiyatsa/ruangbacainformatika/commit/f2bcdf8a02493d5b1d3125cf4e5470940322ef4d))
* create PublicInfoCard and PublicPageSection components for layout ([b936c0d](https://github.com/ghiyatsa/ruangbacainformatika/commit/b936c0d43a218169a252a48b9186906a5095cf28))
* implement animated Hero section and SimilarityController for welcome page ([ab8b7d5](https://github.com/ghiyatsa/ruangbacainformatika/commit/ab8b7d52548fab698db62115e37cbb06fa30ee9e))
* implement authorization policies for core application models ([5138a2a](https://github.com/ghiyatsa/ruangbacainformatika/commit/5138a2ac10f109a6a0477d5351797f31c4909cd7))
* implement automated email reminders for loans due tomorrow ([b65e686](https://github.com/ghiyatsa/ruangbacainformatika/commit/b65e686989ab125ca0b4c863abd7c264db2d4e1a))
* implement base access control policies for application models ([9587b78](https://github.com/ghiyatsa/ruangbacainformatika/commit/9587b784ac374138b054f8c33cb39e71810f56ff))
* implement base model access policies for application entities ([a2624ad](https://github.com/ghiyatsa/ruangbacainformatika/commit/a2624ad1af1aa2ca03b1f21c371bd2a19c7e43a6))
* implement book detail page component with availability status and metadata display ([7125d85](https://github.com/ghiyatsa/ruangbacainformatika/commit/7125d858d2f51a7908984ed8cdaf02d30427bbcd))
* implement book detail page component with availability status and metadata display ([c993029](https://github.com/ghiyatsa/ruangbacainformatika/commit/c993029a46c28f4766a3e6974a8ff3a2a7680875))
* implement book image processing service, data import functionality, and structured Filament forms and tables ([72eec52](https://github.com/ghiyatsa/ruangbacainformatika/commit/72eec52d179c91a41b8aaac78a01a7781c39ad8b))
* implement book import functionality with ISBN normalization and support for related data models. ([a2ad81c](https://github.com/ghiyatsa/ruangbacainformatika/commit/a2ad81c783536cda4c114788d9aa603e213fc47d))
* implement catalog pages for books and skripsi including filtering, search, and detail views ([a5366af](https://github.com/ghiyatsa/ruangbacainformatika/commit/a5366af964e4a094ac766863acacc2eb346be32b))
* implement catalog reports and contact messaging systems with Filament admin resources and UI components ([fdf97c5](https://github.com/ghiyatsa/ruangbacainformatika/commit/fdf97c5f1a2e35d26c502d1c06845b7b7cd6f63e))
* implement catalog system with enhanced book display, search, and filtration components ([f3d6608](https://github.com/ghiyatsa/ruangbacainformatika/commit/f3d66088813bc36c6c89e68faeba3480ecef5a7c))
* implement comprehensive authorization policies for core system models ([f443b02](https://github.com/ghiyatsa/ruangbacainformatika/commit/f443b0268e0b6471c505289c2dcef97739c8d4c5))
* implement comprehensive catalog features, WhatsApp notification system, and React-based UI architecture with optimized build configuration. ([2537724](https://github.com/ghiyatsa/ruangbacainformatika/commit/2537724b755685a963a435adb6646134d0740d4d))
* implement comprehensive Filament dashboard widgets and resource structures for library operations ([aef5b65](https://github.com/ghiyatsa/ruangbacainformatika/commit/aef5b6549d29be224961b0f038fb6dd69ec24fc6))
* implement comprehensive kiosk flow, authentication system, and developer documentation skills ([3c620a1](https://github.com/ghiyatsa/ruangbacainformatika/commit/3c620a1f1384e731a6afe64d0b4be607e620001b))
* implement comprehensive kiosk system, library circulation features, and WhatsApp-based authentication with user profile management ([d00c4a9](https://github.com/ghiyatsa/ruangbacainformatika/commit/d00c4a98c1fbef71042655c5d76090a1d59cd276))
* implement comprehensive landing page, catalog views, and layout architecture ([fb5f000](https://github.com/ghiyatsa/ruangbacainformatika/commit/fb5f000d3e60faf5238f6d44dd067f637665178a))
* implement comprehensive security headers and modernize frontend catalog components ([600b213](https://github.com/ghiyatsa/ruangbacainformatika/commit/600b213bf319ae5a0ddb0a527185621852a29502))
* implement comprehensive user authentication, profile management, and modular Filament resource administration system ([aaa44fe](https://github.com/ghiyatsa/ruangbacainformatika/commit/aaa44fee9a888b3ee8c0d2275ca8fb517e283fba))
* implement core book browsing, loan management, and user authentication features with associated UI components and Filament resources. ([d1481f9](https://github.com/ghiyatsa/ruangbacainformatika/commit/d1481f9311a5ff0a210813d408c3a7bd4764d384))
* implement core feature set including auth flows, catalog UI, kiosk functionality, and admin panel testing suite ([33ce62a](https://github.com/ghiyatsa/ruangbacainformatika/commit/33ce62a239a0ca7e5bf7e46e652f151958ea8d15))
* implement core library catalog features, user settings, and application layout components ([33fe9e9](https://github.com/ghiyatsa/ruangbacainformatika/commit/33fe9e952d5251dad0bc194350d14654bc01e19a))
* implement core resource library UI components, skeletons, and layout architecture ([7d8d1bf](https://github.com/ghiyatsa/ruangbacainformatika/commit/7d8d1bf769d40299a15b346de873fd2da05aefb8))
* implement core resource management modules, similarity synchronization, and refined frontend UI components ([d6b41a2](https://github.com/ghiyatsa/ruangbacainformatika/commit/d6b41a2617d54c302dcbb1460e68434719974b10))
* implement dynamic Open Graph image generation and add loan activity chart widget ([1c35823](https://github.com/ghiyatsa/ruangbacainformatika/commit/1c35823055ab6e307c4a82d64a2d7fb4e18e284e))
* implement dynamic Open Graph image generation for site and catalog pages ([566a894](https://github.com/ghiyatsa/ruangbacainformatika/commit/566a8949b368f25460b7d26910d3671e1e7fa131))
* implement dynamic OpenGraph image generation service with associated tests and assets ([2141891](https://github.com/ghiyatsa/ruangbacainformatika/commit/21418916927353ad577980cee84d6056baa522bd))
* implement Filament tables for categories, authors, and publishers, and configure filesystem disk for book cover storage. ([425c592](https://github.com/ghiyatsa/ruangbacainformatika/commit/425c5921d5208ddb158a26a56dcae6cea1579eab))
* implement full-stack frontend catalog, search, and user settings features ([c47c2e3](https://github.com/ghiyatsa/ruangbacainformatika/commit/c47c2e383132c10f37955ae40e1b509eb0741447))
* implement full-stack kiosk circulation system with return draft management and library services ([81b5bee](https://github.com/ghiyatsa/ruangbacainformatika/commit/81b5bee1c64f5042a90e70f970fda01d19272a6f))
* implement full-stack kiosk system, catalog features, user settings dashboard, and similarity search module ([36ed9d4](https://github.com/ghiyatsa/ruangbacainformatika/commit/36ed9d4a4563877a1835a0962babec784e370e97))
* implement full-stack library management system with Filament resources, Inertia.js frontend, and automated testing ([1fe9cb0](https://github.com/ghiyatsa/ruangbacainformatika/commit/1fe9cb010ce52c3c67d6b6a71ed7509e06913834))
* implement full-stack WhatsApp notification system and reorganize Filament resource schemas and tables ([a91e047](https://github.com/ghiyatsa/ruangbacainformatika/commit/a91e0479c24bbe24e9f45f528a13901c20103f33))
* implement Google One Tap sign-in and standard OAuth authentication flow ([4373769](https://github.com/ghiyatsa/ruangbacainformatika/commit/43737690a5019cd7382ed3fb0f624f99546ed041))
* implement granular model authorization policies and add VelocityScroll UI component ([94bd58e](https://github.com/ghiyatsa/ruangbacainformatika/commit/94bd58e0031c45c641ae27598eee0aff218cee2a))
* implement home page catalog view with ISSN, borrowability, and book availability logic. ([6606b2d](https://github.com/ghiyatsa/ruangbacainformatika/commit/6606b2dd7cc4fb70d3f7dc0643c92c53ba425d31))
* implement home page dashboard with catalog stats, deferred category loading, and site-wide notifications ([474cff1](https://github.com/ghiyatsa/ruangbacainformatika/commit/474cff1b1cf7b6168cebf046105629ad8ec883e9))
* implement integration settings page and similarity check controller with caching and validation logic ([5cc3423](https://github.com/ghiyatsa/ruangbacainformatika/commit/5cc34231cbc227226b12c5ad86f48432fdb00d1f))
* implement internship report management system and add overdue loan tracking widget ([a129894](https://github.com/ghiyatsa/ruangbacainformatika/commit/a1298943ef7cf2c97cd0a25f53367e57a2753b02))
* implement kiosk device management, catalog filtering, and multi-factor authentication systems ([5574a0f](https://github.com/ghiyatsa/ruangbacainformatika/commit/5574a0f849580cf6bc37a33346cec4e88eb6af89))
* implement kiosk interface for book borrowing and returning functionality ([71dc7f8](https://github.com/ghiyatsa/ruangbacainformatika/commit/71dc7f89caf5c25165dd35b3a1284a4c76e0ba35))
* implement kiosk interface, general settings cluster, and categorized book browsing with filtering. ([2c5b44e](https://github.com/ghiyatsa/ruangbacainformatika/commit/2c5b44ef29ca2af3ac27997524a6ace9fa479259))
* implement kiosk loan management system, email verification OTP flow, and shared Inertia request middleware ([ba66599](https://github.com/ghiyatsa/ruangbacainformatika/commit/ba66599278fb36009b745ff1f3cb6e9c1da51465))
* implement kiosk loan service, PIN access management, and automated return reminder system with comprehensive feature tests ([7c14b1b](https://github.com/ghiyatsa/ruangbacainformatika/commit/7c14b1b93235bb38dce87058b7a096bdefdfd6d7))
* implement kiosk PIN management, resource policies, models, and administrative scaffolding ([1df2e60](https://github.com/ghiyatsa/ruangbacainformatika/commit/1df2e602e267f5f021e2ee73b7541298fa2b1286))
* implement library kiosk system including member registration, book search, and visitor logging functionality ([48df23a](https://github.com/ghiyatsa/ruangbacainformatika/commit/48df23a81945a93ee7c910f2ff1422acfd861d29))
* implement modular catalog system and similarity synchronization infrastructure ([e7d363e](https://github.com/ghiyatsa/ruangbacainformatika/commit/e7d363ef60ae24606f37e408dc7b19dbd92d25a4))
* implement modular Filament table and form schemas for user management ([7b9d32c](https://github.com/ghiyatsa/ruangbacainformatika/commit/7b9d32cf72e18f08b01e9e7168850a468d2729e4))
* implement OpenGraphImage generator service and controller for dynamic social preview images ([c2155c6](https://github.com/ghiyatsa/ruangbacainformatika/commit/c2155c6581254d50967ca8a0fb8c02496728c638))
* implement profile management system with validation and verification workflows ([814d6f8](https://github.com/ghiyatsa/ruangbacainformatika/commit/814d6f878c6b3426201ee0048c195ddaede6ae85))
* implement profile onboarding flow and add Google authentication feature tests ([a1b9fa2](https://github.com/ghiyatsa/ruangbacainformatika/commit/a1b9fa252ad95922653e853306f81dff254d686a))
* implement queued email notifications for loan receipts and OTP verification, and update build scripts for PHP environment compatibility ([6f2ff9d](https://github.com/ghiyatsa/ruangbacainformatika/commit/6f2ff9dcb2737b9a8de356f88fc8d309e092f348))
* implement resource access authorization policies for all core system models ([f518d4a](https://github.com/ghiyatsa/ruangbacainformatika/commit/f518d4a20a68e35560a0aebedcda8349323b9edd))
* implement resource policies and add admin panel feature tests ([a11caf5](https://github.com/ghiyatsa/ruangbacainformatika/commit/a11caf513d732e70177830858e870ee44cf611b3))
* implement resource policies and add ShieldSeeder for role-based access control ([ca4883b](https://github.com/ghiyatsa/ruangbacainformatika/commit/ca4883bb439e0b6524bbabf9505e106106105d8b))
* implement similarity sync management system and add various Filament admin widgets ([f1b610c](https://github.com/ghiyatsa/ruangbacainformatika/commit/f1b610c77d61176389f3fc8b59592125e41b5d9b))
* implement similarity sync service with queue management and extend user profile validation ([60e859e](https://github.com/ghiyatsa/ruangbacainformatika/commit/60e859e0c5c476d4f3e427b294b61e18973dfd0e))
* implement skripsi catalog and search functionality with new UI components ([43ef9aa](https://github.com/ghiyatsa/ruangbacainformatika/commit/43ef9aa40e0d04d4fab89356e900618498cecb8a))
* implement Skripsi resource management and catalog browse features with new UI components and settings pages ([93170d4](https://github.com/ghiyatsa/ruangbacainformatika/commit/93170d423db0dfc6e140ba233898b128484db6b4))
* implement static pages, activity logging, and site configuration with updated seeders and frontend components ([f32d404](https://github.com/ghiyatsa/ruangbacainformatika/commit/f32d404b31fe20ca784b3514e3f486a98975e150))
* implement static pages, book management utilities, and interactive team profile page ([09e839d](https://github.com/ghiyatsa/ruangbacainformatika/commit/09e839d840da829ad6099c23f63090e3a32198de))
* implement SuperAdminSeeder and update deployment commands to seed initial admin user ([13e1d3f](https://github.com/ghiyatsa/ruangbacainformatika/commit/13e1d3f4fbda791dd740f235d8cfdc95990e3b3e))
* implement thesis resource management, catalog pages, and developer skill documentation ([c9b9426](https://github.com/ghiyatsa/ruangbacainformatika/commit/c9b9426af1ac4cda56501f5f31267a8577f803f6))
* implement UI components, skeleton loaders, and appearance hook for layout and theme management ([92d422a](https://github.com/ghiyatsa/ruangbacainformatika/commit/92d422a94dfe683fc201d95e71885a0409c126fb))
* improve accessibility and performance by enhancing catalog pagination, optimizing resource detail rendering, and introducing OG image support. ([72ce27b](https://github.com/ghiyatsa/ruangbacainformatika/commit/72ce27b6f9706f79e3f4b8dd75f9932af320785b))
* initialize application providers and configure custom middleware, routing, and security defaults ([c470e18](https://github.com/ghiyatsa/ruangbacainformatika/commit/c470e186abe7aee24bacadb5a629a48105e0a8ab))
* initialize default filesystem configuration with local, public, and s3 disks ([d71809b](https://github.com/ghiyatsa/ruangbacainformatika/commit/d71809b2de07ed13c6494b4cd672d28d10738dfc))
* initialize project structure with Filament resources, React-based frontend scaffolding, and development workflows. ([3bda3ff](https://github.com/ghiyatsa/ruangbacainformatika/commit/3bda3ff85b6388b320184e742fc24dafbb3b8777))
* integrate Resend for email services and implement custom user registration logic with feature tests ([8de5dc0](https://github.com/ghiyatsa/ruangbacainformatika/commit/8de5dc0074b91424780bf7c76403d954512ac737))
* scaffold application layout, loan request system, and detail pages with notice component support ([8e96c44](https://github.com/ghiyatsa/ruangbacainformatika/commit/8e96c44899f121fb2c7a2b41f33ff63ebf879461))
* scaffold core frontend UI components, page layouts, and resource detail features ([275d905](https://github.com/ghiyatsa/ruangbacainformatika/commit/275d9056ffeeaaa1a5696f9717da3c6781c9140d))
* scaffold core library features including catalog management, similarity engine, loan processing, and Filament admin infrastructure ([925b543](https://github.com/ghiyatsa/ruangbacainformatika/commit/925b543da1a6a451b9af2b3ad06103bb3149f31c))
* update tooltip position in Skripsi and Thesis cards to top ([b936c0d](https://github.com/ghiyatsa/ruangbacainformatika/commit/b936c0d43a218169a252a48b9186906a5095cf28))


### Bug Fixes

* add railpack.json with required PHP extensions (intl, zip, exif) ([321e9bd](https://github.com/ghiyatsa/ruangbacainformatika/commit/321e9bdddcd87fdc514c1ee8fbabe7ac4f321569))
* ensure tsconfig.json ends with a newline ([b936c0d](https://github.com/ghiyatsa/ruangbacainformatika/commit/b936c0d43a218169a252a48b9186906a5095cf28))
* format view count in Skripsi and Thesis detail pages ([b936c0d](https://github.com/ghiyatsa/ruangbacainformatika/commit/b936c0d43a218169a252a48b9186906a5095cf28))
* make borrowing restriction scope compatible with sqlite ([efa47ad](https://github.com/ghiyatsa/ruangbacainformatika/commit/efa47adbe25e804f8e41527676bf528092937b83))
* resolve undefined method getDriverName static analysis warning ([e7fa1fa](https://github.com/ghiyatsa/ruangbacainformatika/commit/e7fa1fa40d762453b487c4b4d7ec6505fccec337))
* update TypeScript configuration to ignore deprecations for version 5.0 and refactor imports in authentication components ([a559f6f](https://github.com/ghiyatsa/ruangbacainformatika/commit/a559f6f9b67d0ac38379c1fe6c97b9c53e8fb3f0))

## Changelog

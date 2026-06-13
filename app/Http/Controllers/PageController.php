<?php

namespace App\Http\Controllers;

use App\Support\StaticPageContent;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function __construct(
        protected StaticPageContent $staticPageContent,
    ) {}

    public function about(): Response
    {
        return Inertia::render('about/index', [
            'pageContent' => $this->staticPageContent->about(),
        ]);
    }

    public function aboutTeam(): Response
    {
        return Inertia::render('about/team');
    }

    public function contact(): Response
    {
        return Inertia::render('contact/index');
    }

    public function privacyPolicy(): Response
    {
        return Inertia::render('legal/privacy', [
            'pageContent' => $this->staticPageContent->privacyPolicy(),
        ]);
    }

    public function termsOfService(): Response
    {
        return Inertia::render('legal/terms', [
            'pageContent' => $this->staticPageContent->termsOfService(),
        ]);
    }

    public function show(string $slug): Response
    {
        $page = $this->staticPageContent->customPage($slug);

        abort_unless($page !== null, HttpResponse::HTTP_NOT_FOUND);

        return Inertia::render('static/index', [
            'title' => $page->title,
            'pageContent' => [
                'summary' => $page->summary,
                'content' => $page->content,
            ],
        ]);
    }
}

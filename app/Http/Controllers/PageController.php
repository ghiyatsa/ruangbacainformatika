<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function about(): Response
    {
        return Inertia::render('about');
    }

    public function aboutTeam(): Response
    {
        return Inertia::render('about-team');
    }

    public function contact(): Response
    {
        return Inertia::render('contact');
    }

    public function privacyPolicy(): Response
    {
        return Inertia::render('privacy-policy');
    }

    public function termsOfService(): Response
    {
        return Inertia::render('terms-of-service');
    }
}

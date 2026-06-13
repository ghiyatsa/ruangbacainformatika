<?php

namespace App\Http\Controllers;

use App\Actions\Similarity\CheckSimilarity;
use App\Http\Requests\CheckSimilarityRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class SimilarityController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('similarity/index', [
            'turnstileEnabled' => config('services.turnstile.enabled', false),
            'turnstileSiteKey' => config('services.turnstile.key'),
        ]);
    }

    public function check(CheckSimilarityRequest $request, CheckSimilarity $checkSimilarity): JsonResponse
    {
        return $checkSimilarity->execute($request->validatedJudul(), $request->user()?->id);
    }
}
